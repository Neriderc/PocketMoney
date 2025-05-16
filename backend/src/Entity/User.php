<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Controller\AddUserController;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username'])]
#[ApiResource(
    operations: [
        new Get(
            normalizationContext: ['groups' => ['user:read']],
            security: "is_granted('ROLE_ADMIN')"
        ),
        new GetCollection(
            normalizationContext: ['groups' => ['user:read']],
            security: "is_granted('ROLE_ADMIN')"
        ),
        new Patch(
            normalizationContext: ['groups' => ['user:write']],
            security: "is_granted('ROLE_ADMIN')"
        ),
        new Post(
            controller: AddUserController::class,
            normalizationContext: ['groups' => ['user:write']],
        ),
        new Delete(
            security: "is_granted('ROLE_ADMIN')"
        )
    ]
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Groups(['user:read', 'user:write'])]
    #[Assert\NotBlank(message: "Username cannot be blank")]
    #[Assert\Length(min: 1, max: 180)]
    #[Assert\Regex(
        pattern: '/^[\p{L}0-9 \'-]+$/u',
        message: "Name can only contain letters, numbers, spaces, hyphens, and apostrophes"
    )]
    private ?string $username = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    #[Groups(['user:read', 'user:write'])]
    #[Assert\All([
        new Assert\Choice(choices: ['ROLE_ADMIN', 'ROLE_USER'], message: 'The role "{{ value }}" is not a valid role.')
    ])]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\OneToOne(mappedBy: 'linkedUser', cascade: ['persist'])]
    #[Groups(['user:read'])]
    private ?Child $linkedChild = null;

    #[ORM\ManyToOne]
    #[Groups(['user:read'])]
    #[ORM\JoinColumn(onDelete: "SET NULL")]
    private ?Household $defaultHousehold = null;

    /**
     * @var Collection<int, Household>
     */
    #[ORM\ManyToMany(targetEntity: Household::class, inversedBy: 'users', cascade: ['persist'])]
    #[Groups(['user:read', 'user:write'])]
    private Collection $households;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['user:read', 'user:write'])]
    #[Assert\Length(max: 255)]
    private ?string $email = null;

    public function __construct()
    {
        $this->households = new ArrayCollection();
    }
    

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getLinkedChild(): ?Child
    {
        return $this->linkedChild;
    }

    public function setLinkedChild(?Child $linkedChild): static
    {
        // unset the owning side of the relation if necessary
        if ($linkedChild === null && $this->linkedChild !== null) {
            $this->linkedChild->setLinkedUser(null);
        }

        // set the owning side of the relation if necessary
        if ($linkedChild !== null && $linkedChild->getLinkedUser() !== $this) {
            $linkedChild->setLinkedUser($this);
        }

        $this->linkedChild = $linkedChild;

        return $this;
    }

    public function getDefaultHousehold(): ?Household
    {
        return $this->defaultHousehold;
    }

    public function setDefaultHousehold(?Household $defaultHousehold): static
    {
        $this->defaultHousehold = $defaultHousehold;

        return $this;
    }

    /**
     * @return Collection<int, Household>
     */
    public function getHouseholds(): Collection
    {
        return $this->households;
    }

    public function addHousehold(Household $household): static
    {
        if (!$this->households->contains($household)) {
            $this->households->add($household);
        }

        return $this;
    }

    public function removeHousehold(Household $household): static
    {
        $this->households->removeElement($household);

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function isAdmin(): bool
    {
        return in_array('ROLE_ADMIN', $this->roles);
    }
}
