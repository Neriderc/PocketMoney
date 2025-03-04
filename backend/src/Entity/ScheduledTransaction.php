<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Controller\AddTransactionScheduleController;
use App\Enum\AmountBase;
use App\Enum\RepeatFrequency;
use App\Repository\ScheduledTransactionRepository;
use App\State\ScheduledTransactionCollectionProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ScheduledTransactionRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/children/{childId}/scheduled_transactions',
            uriVariables: [
                'childId' => new Link(toProperty: 'child', fromClass: Child::class),
            ],
            paginationEnabled: true,
            paginationClientItemsPerPage: true,
            provider: ScheduledTransactionCollectionProvider::class,
        ),
        new Get(
            uriTemplate: '/children/{childId}/scheduled_transactions/{id}',
            uriVariables: [
                'childId' => new Link(toProperty: 'child', fromClass: Child::class),
                'id' => new Link(fromClass: ScheduledTransaction::class),
            ],
            normalizationContext: ['groups' => ['scheduled_transactions:details']],
            security: "is_granted('ROLE_ADMIN') or object.getChild().getHousehold().getUsers().contains(user) or object.getChild().getLinkedUser() === user"
        ),
        new Post(
            uriTemplate: '/children/{childId}/scheduled_transactions',
            uriVariables: [
                'childId' => new Link(toProperty: 'child', fromClass: Child::class),
            ],
            controller: AddTransactionScheduleController::class,
        ),
        new Patch(
            uriTemplate: '/children/{childId}/scheduled_transactions/{id}',
            uriVariables: [
                'childId' => new Link(toProperty: 'child', fromClass: Child::class),
                'id' => new Link(fromClass: ScheduledTransaction::class),
            ],
            normalizationContext: ['groups' => ['scheduled_transactions:details']],
            denormalizationContext: ['groups' => ['scheduled_transactions:update']],
            security: "is_granted('ROLE_ADMIN') or object.getChild().getHousehold().getUsers().contains(user)"
        ),
        new Delete(
            uriTemplate: '/children/{childId}/scheduled_transactions/{id}',
            uriVariables: [
                'childId' => new Link(toProperty: 'child', fromClass: Child::class),
                'id' => new Link(fromClass: ScheduledTransaction::class),
            ],
            security: "is_granted('ROLE_ADMIN') or object.getChild().getHousehold().getUsers().contains(user)"
        ),
    ]
)]
class ScheduledTransaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['scheduled_transactions:details'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['scheduled_transactions:details', 'scheduled_transactions:update'])]
    private ?float $amount = null;

    #[ORM\Column]
    #[Groups(['scheduled_transactions:details'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['scheduled_transactions:details'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 255)]
    #[Groups(['scheduled_transactions:details', 'scheduled_transactions:update'])]
    #[Assert\NotBlank(message: "Description cannot be blank")]
    #[Assert\Length(min: 1, max: 255)]
    #[Assert\Regex(
        pattern: '/^[\p{L} \'-]+$/u',
        message: "Description can only contain letters, spaces, hyphens, and apostrophes"
    )]
    private ?string $description = null;

    #[ORM\Column(length: 2000, nullable: true)]
    #[Groups(['scheduled_transactions:details', 'scheduled_transactions:update'])]
    #[Assert\Length(max: 2000)]
    private ?string $comment = null;

    #[ORM\Column]
    #[Groups(['scheduled_transactions:details', 'scheduled_transactions:update'])]
    #[Assert\NotNull]
    #[Assert\NotNull]
    private ?\DateTimeImmutable $nextExecutionDate = null;

    #[ORM\Column(length: 255)]
    #[Groups(['scheduled_transactions:details', 'scheduled_transactions:update'])]
    private AmountBase $amountBase = AmountBase::FIXED;

    #[ORM\ManyToOne(inversedBy: 'transactionSchedules')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Child $child = null;

    /**
     * @var Collection<int, Account>
     */
    #[ORM\ManyToMany(targetEntity: Account::class, inversedBy: 'transactionSchedules')]
    #[Groups(['scheduled_transactions:details', 'scheduled_transactions:update'])]
    #[Assert\Count(min: 1, minMessage: "At least one account must be selected.")]
    private Collection $accounts;

    #[ORM\Column(type: 'string', length: 255, nullable: true,  enumType: RepeatFrequency::class)]
    #[Groups(['scheduled_transactions:details', 'scheduled_transactions:update'])]
    private ?RepeatFrequency $repeatFrequency = RepeatFrequency::WEEKLY;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->accounts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): static
    {
        $this->amount = $amount;

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
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }

    public function getNextExecutionDate(): ?\DateTimeImmutable
    {
        return $this->nextExecutionDate;
    }

    public function setNextExecutionDate(\DateTimeImmutable $nextExecutionDate): static
    {
        $this->nextExecutionDate = $nextExecutionDate;

        return $this;
    }

    public function getAmountBase(): AmountBase
    {
        return $this->amountBase;
    }

    public function setAmountBase(AmountBase $amountBase): static
    {
        $this->amountBase = $amountBase;

        return $this;
    }

    public function getChild(): ?Child
    {
        return $this->child;
    }

    public function setChild(?Child $child): static
    {
        $this->child = $child;

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
        }

        return $this;
    }

    public function removeAccount(Account $account): static
    {
        $this->accounts->removeElement($account);

        return $this;
    }

    public function getRepeatFrequency(): ?RepeatFrequency
    {
        return $this->repeatFrequency;
    }

    public function setRepeatFrequency(RepeatFrequency $repeatFrequency): static
    {
        $this->repeatFrequency = $repeatFrequency;

        return $this;
    }
}
