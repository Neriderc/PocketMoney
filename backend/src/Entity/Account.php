<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Controller\AddAccountController;
use App\Controller\GetAccountsController;
use App\Repository\AccountRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An account that belongs to a child for tracking transactions and a total balance.
 */
#[ORM\Entity(repositoryClass: AccountRepository::class)]
#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/children/{childId}/accounts/{accountId}',
            uriVariables: [
                'accountId' => new Link(fromClass: Account::class),
                'childId' => new Link(toProperty: 'child', fromClass: Child::class),
            ],
            normalizationContext: ['groups' => ['accounts:details']],
            security: "is_granted('ROLE_ADMIN') or object.getChild().getHousehold().getUsers().contains(user) or object.getChild().getLinkedUser() === user",),
        new GetCollection(
            uriTemplate: '/children/{childId}/accounts',
            uriVariables: [
                'childId' => new Link(toProperty: 'child', fromClass: Child::class),
            ],
            controller: GetAccountsController::class,
            paginationEnabled: false,
            normalizationContext: ['groups' => ['accounts:list']],
            read: true,
            name: 'accounts'
        ),
        new Patch(
            uriTemplate: '/children/{childId}/accounts/{accountId}',
            uriVariables: [
                'accountId' => new Link(fromClass: Account::class),
                'childId' => new Link(toProperty: 'child', fromClass: Child::class),
            ],
            normalizationContext: ['groups' => ['accounts:details']],
            denormalizationContext: ['groups' => ['accounts:update']],
            security: "is_granted('ROLE_ADMIN') or object.getChild().getHousehold().getUsers().contains(user)",
        ),
        new Delete(
            uriTemplate: '/children/{childId}/accounts/{accountId}',
            uriVariables: [
                'accountId' => new Link(fromClass: Account::class),
                'childId' => new Link(toProperty: 'child', fromClass: Child::class),
            ],
            security: "is_granted('ROLE_ADMIN') or object.getChild().getHousehold().getUsers().contains(user)",
        ),
        new Post(
            uriTemplate: '/children/{childId}/accounts',
            uriVariables: [
                'childId' => new Link(toProperty: 'child', fromClass: Child::class),
            ],
            controller: AddAccountController::class,
        )
    ]
)]
class Account
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['accounts:list', 'accounts:details'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Name cannot be blank")]
    #[Assert\Length(min: 1, max: 255)]
    #[Assert\Regex(
        pattern: '/^[\p{L} \'-]+$/u',
        message: "Name can only contain letters, spaces, hyphens, and apostrophes"
    )]
    #[Groups(['accounts:list', 'accounts:details', 'accounts:update', 'scheduled_transactions:details'])]
    private ?string $name = null;

    #[ORM\Column]
    #[Groups(['accounts:list', 'accounts:details'])]
    #[Assert\NotNull]
    #[Assert\Type('float')]
    private ?float $balance = 0;

    #[ORM\ManyToOne(cascade: ['persist'], inversedBy: 'accounts')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private ?Child $child = null;

    /**
     * @var Collection<int, Transaction>
     */
    #[ORM\OneToMany(targetEntity: Transaction::class, mappedBy: 'account', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $transactions;

    #[ORM\Column]
    #[Groups(['accounts:details'])]
    private ?\DateTimeImmutable $createdAt;

    #[ORM\Column]
    #[Groups(['accounts:details'])]
    private ?\DateTimeImmutable $updatedAt;

    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\Length(max: 50)]
    #[Groups(['accounts:list', 'accounts:details', 'accounts:update'])]
    private ?string $icon = null;

    #[ORM\Column(length: 7, nullable: true)]
    #[Assert\Length(max: 7)]
    #[Assert\Regex(
        pattern: '/^#([a-fA-F0-9]{3}|[a-fA-F0-9]{6})$/',
        message: 'Color must be a valid hex code like #fff or #ffffff.'
    )]
    #[Groups(['accounts:list', 'accounts:details', 'accounts:update'])]
    private ?string $color = null;

    /**
     * @var Collection<int, ScheduledTransaction>
     */
    #[ORM\ManyToMany(targetEntity: ScheduledTransaction::class, mappedBy: 'accounts')]
    private Collection $transactionSchedules;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->transactions = new ArrayCollection();
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
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getBalance(): ?float
    {
        return $this->balance;
    }

    public function setBalance(float $balance): static
    {
        $this->balance = $balance;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getChild(): ?Child
    {
        return $this->child;
    }

    public function setChild(?Child $child): static
    {
        $this->child = $child;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    /**
     * @return Collection<int, Transaction>
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function addTransaction(Transaction $transaction): static
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions->add($transaction);
            $transaction->setAccount($this);
        }

        return $this;
    }

    public function removeTransaction(Transaction $transaction): static
    {
        if ($this->transactions->removeElement($transaction)) {
            // set the owning side to null (unless already changed)
            if ($transaction->getAccount() === $this) {
                $transaction->setAccount(null);
            }
        }

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

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): static
    {
        $this->icon = $icon;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): static
    {
        $this->color = $color;
        $this->updatedAt = new \DateTimeImmutable();
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
            $transactionSchedule->addAccount($this);
        }

        return $this;
    }

    public function removeTransactionSchedule(ScheduledTransaction $transactionSchedule): static
    {
        if ($this->transactionSchedules->removeElement($transactionSchedule)) {
            $transactionSchedule->removeAccount($this);
        }

        return $this;
    }
}