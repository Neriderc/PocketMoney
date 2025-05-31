<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Controller\AddTransactionController;
use App\Controller\DeleteTransactionController;
use App\Repository\TransactionRepository;
use App\State\TransactionCollectionProvider;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A transaction that takes place within an account.
 */
#[ORM\Entity(repositoryClass: TransactionRepository::class)]
#[ORM\Table(name: '`transactions`')]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/accounts/{id}/transactions',
            uriVariables: [
                'id' => new Link(toProperty: 'account', fromClass: Account::class),
            ],
            paginationEnabled: true,
            paginationClientItemsPerPage: true,
            order: ['transactionDate' => 'DESC'],
            provider: TransactionCollectionProvider::class,
        ),
        new Get(
            uriTemplate: '/accounts/{accountId}/transactions/{id}',
            uriVariables: [
                'accountId' => new Link(toProperty: 'account', fromClass: Account::class),
                'id' => new Link(fromClass: Transaction::class),
            ],
            normalizationContext: ['groups' => ['transactions:details']],
            security: "is_granted('ROLE_ADMIN') or object.getAccount().getChild().getHousehold().getUsers().contains(user) or object.getAccount().getChild().getLinkedUser() === user"
        ),
        new Post(
            uriTemplate: '/accounts/{accountId}/transactions',
            uriVariables: [
                'accountId' => new Link(toProperty: 'account', fromClass: Account::class),
            ],
            controller: AddTransactionController::class,
            read: false,
        ),
        new Patch(
            uriTemplate: '/accounts/{accountId}/transactions/{id}',
            uriVariables: [
                'accountId' => new Link(toProperty: 'account', fromClass: Account::class),
                'id' => new Link(fromClass: Transaction::class),
            ],
            normalizationContext: ['groups' => ['transactions:details']],
            denormalizationContext: ['groups' => ['transactions:update']],
            security: "is_granted('ROLE_ADMIN') or object.getAccount().getChild().getHousehold().getUsers().contains(user)"
        ),
        new Delete(
            uriTemplate: '/accounts/{accountId}/transactions/{transactionId}',
            controller: DeleteTransactionController::class,
            name: 'delete_transaction'
        ),
    ]
)]
    class Transaction
    {
        #[ORM\Id]
        #[ORM\GeneratedValue]
        #[ORM\Column]
        #[Groups(['transactions:details', 'transactions:update'])]
        private ?int $id = null;

        #[ORM\Column]
        #[Groups(['transactions:details', 'transactions:update'])]
        private ?float $amount = null;

        #[ORM\Column(nullable: true)]
        #[Groups(['transactions:details', 'transactions:update'])]
        private ?DateTimeImmutable $transactionDate = null;

        #[ORM\ManyToOne(cascade: ['persist'], inversedBy: 'transactions')]
        #[ORM\JoinColumn(nullable: false)]
        private ?Account $account = null;

        #[ORM\Column]
        #[Groups(['transactions:details'])]
        private ?DateTimeImmutable $createdAt;

        #[ORM\Column]
        #[Groups(['transactions:details'])]
        private ?DateTimeImmutable $updatedAt;

        #[ORM\Column(length: 255)]
        #[Groups(['transactions:details', 'transactions:update'])]
        #[Assert\NotBlank(message: "Description cannot be blank")]
        #[Assert\Length(min: 1, max: 255)]
        #[Assert\Regex(
            pattern: '/^[\p{L}0-9 \'-]+$/u',
            message: "Name can only contain letters, numbers, spaces, hyphens, and apostrophes"
        )]
        private ?string $description = null;

        #[ORM\Column(length: 2000, nullable: true)]
        #[Groups(['transactions:details', 'transactions:update'])]
        #[Assert\Length(max: 2000)]
        private ?string $comment = null;

        public function __construct()
        {
            $this->createdAt = new DateTimeImmutable();
            $this->updatedAt = new DateTimeImmutable();
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

        public function getTransactionDate(): ?DateTimeImmutable
        {
            return $this->transactionDate;
        }

        public function setTransactionDate(?DateTimeImmutable $transactionDate): static
        {
            $this->transactionDate = $transactionDate;

            return $this;
        }

        public function getAccount(): ?Account
        {
            return $this->account;
        }

        public function setAccount(?Account $account): static
        {
            $this->account = $account;

            return $this;
        }

        public function getCreatedAt(): ?DateTimeImmutable
        {
            return $this->createdAt;
        }

        private function setCreatedAt(DateTimeImmutable $createdAt): static
        {
            $this->createdAt = $createdAt;

            return $this;
        }

        public function getUpdatedAt(): ?DateTimeImmutable
        {
            return $this->updatedAt;
        }

        private function setUpdatedAt(DateTimeImmutable $updatedAt): static
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

        #[ORM\PreUpdate]
        public function onPreUpdate(): void
        {
            $this->updatedAt = new DateTimeImmutable();
        }
    }
