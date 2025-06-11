<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Controller\AddWishlistItemController;
use App\Controller\DeleteWishlistItemController;
use App\Repository\WishlistItemRepository;
use App\State\WishlistItemCollectionProvider;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: WishlistItemRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/wishlists/{id}/wishlist_items',
            uriVariables: [
                'id' => new Link(toProperty: 'wishlist', fromClass: Wishlist::class),
            ],
            paginationEnabled: true,
            paginationClientItemsPerPage: true,
            order: ['priority' => 'DESC'],
            provider: WishlistItemCollectionProvider::class,
        ),
        new Get(
            uriTemplate: '/wishlists/{wishlistId}/wishlist_items/{id}',
            uriVariables: [
                'wishlistId' => new Link(toProperty: 'wishlist', fromClass: Wishlist::class),
                'id' => new Link(fromClass: WishlistItem::class),
            ],
            normalizationContext: ['groups' => ['wishlist_item:details']],
            security: "is_granted('ROLE_ADMIN') or object.getWishlist().getChild().getHousehold().getUsers().contains(user) or object.getAccount().getChild().getLinkedUser() === user"
        ),
        new Post(
            uriTemplate: '/wishlists/{wishlistId}/wishlist_items',
            uriVariables: [
                'wishlistId' => new Link(toProperty: 'wishlist', fromClass: Wishlist::class),
            ],
            controller: AddWishlistItemController::class,
            read: false,
        ),
        new Patch(
            uriTemplate: '/wishlists/{wishlistId}/wishlist_items/{id}',
            uriVariables: [
                'wishlistId' => new Link(toProperty: 'wishlist', fromClass: Wishlist::class),
                'id' => new Link(fromClass: WishlistItem::class),
            ],
            normalizationContext: ['groups' => ['wishlist_item:details']],
            denormalizationContext: ['groups' => ['wishlist_item:update']],
            security: "is_granted('ROLE_ADMIN') or object.getWishlist().getChild().getHousehold().getUsers().contains(user)"
        ),
        new Delete(
            uriTemplate: '/wishlists/{wishlistId}/wishlist_items/{wishlistItemId}',
            controller: DeleteWishlistItemController::class
        ),
    ]
)]class WishlistItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['wishlist_item:details', 'wishlist_item:update'])]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['wishlist_item:details', 'wishlist_item:update'])]
    private ?float $amount = null;

    #[ORM\Column]
    #[Groups(['wishlist_item:details'])]
    private ?DateTimeImmutable $createdAt;

    #[ORM\Column]
    #[Groups(['wishlist_item:details'])]
    private ?DateTimeImmutable $updatedAt;

    #[ORM\Column(nullable: true)]
    #[Groups(['wishlist_item:details', 'wishlist_item:update'])]
    private ?int $priority = null;

    #[ORM\ManyToOne(inversedBy: 'wishlistItems')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Wishlist $wishlist = null;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(?float $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function setPriority(?int $priority): static
    {
        $this->priority = $priority;

        return $this;
    }

    public function getWishlist(): ?Wishlist
    {
        return $this->wishlist;
    }

    public function setWishlist(?Wishlist $wishlist): static
    {
        $this->wishlist = $wishlist;

        return $this;
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }
}
