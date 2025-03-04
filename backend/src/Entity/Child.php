<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Controller\AddChildController;
use App\Controller\DeleteAccountController;
use App\Repository\ChildRepository;
use App\State\ChildCollectionProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Represents a child in the family. Can be claimed by a user account.
 */
#[ORM\Entity(repositoryClass: ChildRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/households/{householdId}/children',
            uriVariables: [
                'householdId' => new Link(toProperty: 'household', fromClass: Household::class),
            ],
            normalizationContext: ['groups' => ['children:list']],
            provider: ChildCollectionProvider::class),
        new Get(
            normalizationContext: ['groups' => ['children:details']],
            security: "is_granted('ROLE_ADMIN') or object.getHousehold().getUsers().contains(user) or object.getLinkedUser() === user"
        ),
        new Patch(
            denormalizationContext: ['groups' => ['children:update']],
            security: "is_granted('ROLE_ADMIN') or object.getHousehold().getUsers().contains(user)"
        ),
        new Post(
            uriTemplate: '/households/{householdId}/children',
            controller: AddChildController::class,
            name: 'create_child',
        ),
        new Delete(
            security: "is_granted('ROLE_ADMIN') or object.getHousehold().getUsers().contains(user)"
        ),
    ]
)]
class Child
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['children:list', 'children:details', 'children:update', 'children:create', 'accounts:list'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['children:list', 'children:details', 'children:update', 'children:create'])]
    #[Assert\NotBlank(message: "Name cannot be blank")]
    #[Assert\Length(min: 1, max: 255)]
    #[Assert\Regex(
        pattern: '/^[\p{L} \'-]+$/u',
        message: "Name can only contain letters, spaces, hyphens, and apostrophes"
    )]
    private ?string $name = null;

    #[ORM\Column]
    #[Groups(['children:details'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['children:details'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToOne(inversedBy: 'linkedChild', cascade: ['persist', 'remove'])]
    #[Assert\Valid]
    private ?User $linkedUser = null;

    /**
     * @var Collection<int, Account>
     */
    #[ORM\OneToMany(targetEntity: Account::class, mappedBy: 'child', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['accounts:list'])]
    private Collection $accounts;


    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    #[Groups(['children:details', 'children:update'])]
    private ?\DateTimeImmutable $dateOfBirth = null;

    #[ORM\ManyToOne(cascade: ['persist'], inversedBy: 'children')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "Child must belong to a household")]
    private ?Household $household = null;

    /**
     * @var Collection<int, ScheduledTransaction>
     */
    #[ORM\OneToMany(targetEntity: ScheduledTransaction::class, mappedBy: 'child', orphanRemoval: true)]
    private Collection $transactionSchedules;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->accounts = new ArrayCollection();
        $this->transactionSchedules = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    private function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    private function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getLinkedUser(): ?User
    {
        return $this->linkedUser;
    }

    public function setLinkedUser(?User $linkedUser): static
    {
        $this->linkedUser = $linkedUser;

        return $this;
    }

    /**
     * @return Collection<int, Account>
     */
    public function getAccounts(): Collection
    {
        return $this->accounts;
    }

    public function addAccount(Account $account): static
    {
        if (!$this->accounts->contains($account)) {
            $this->accounts->add($account);
            $account->setChild($this);
        }

        return $this;
    }

    public function removeAccount(Account $account): static
    {
        if ($this->accounts->removeElement($account)) {
            // set the owning side to null (unless already changed)
            if ($account->getChild() === $this) {
                $account->setChild(null);
            }
        }

        return $this;
    }

    public function getDateOfBirth(): ?\DateTimeImmutable
    {
        return $this->dateOfBirth;
    }

    public function setDateOfBirth(?\DateTimeImmutable $dateOfBirth): static
    {
        $this->dateOfBirth = $dateOfBirth;

        return $this;
    }

    public function getHousehold(): ?Household
    {
        return $this->household;
    }

    public function setHousehold(?Household $household): static
    {
        $this->household = $household;

        return $this;
    }

    /**
     * @return Collection<int, ScheduledTransaction>
     */
    public function getTransactionSchedules(): Collection
    {
        return $this->transactionSchedules;
    }

    public function addTransactionSchedule(ScheduledTransaction $transactionSchedule): static
    {
        if (!$this->transactionSchedules->contains($transactionSchedule)) {
            $this->transactionSchedules->add($transactionSchedule);
            $transactionSchedule->setChild($this);
        }

        return $this;
    }

    public function removeTransactionSchedule(ScheduledTransaction $transactionSchedule): static
    {
        if ($this->transactionSchedules->removeElement($transactionSchedule)) {
            // set the owning side to null (unless already changed)
            if ($transactionSchedule->getChild() === $this) {
                $transactionSchedule->setChild(null);
            }
        }

        return $this;
    }
}
