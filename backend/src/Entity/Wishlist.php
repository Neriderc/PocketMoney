<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\WishlistRepository;
use App\State\WishlistCollectionProvider;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\Link;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: WishlistRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/children/{childId}/wishlists',
            uriVariables: [
                'childId' => new Link(toProperty: 'child', fromClass: Child::class),
            ],
            normalizationContext: ['groups' => ['wishlist:read']],
            provider: WishlistCollectionProvider::class
        ),
        new Patch(
            uriTemplate: '/children/{childId}/wishlists/{wishlistId}',
            uriVariables: [
                'wishlistId' => new Link(fromClass: Wishlist::class),
                'childId' => new Link(toProperty: 'child', fromClass: Child::class),
            ],
            normalizationContext: ['groups' => ['wishlist:read']],
            denormalizationContext: ['groups' => ['wishlist:update']],
            security: "is_granted('ROLE_ADMIN') or object.getChild().getHousehold().getUsers().contains(user)",
        ),
    ]
)]
class Wishlist
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['wishlist:read'])]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'wishlist', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Child $child = null;

    /**
     * @var Collection<int, WishlistItem>
     */
    #[ORM\OneToMany(targetEntity: WishlistItem::class, mappedBy: 'wishlist', orphanRemoval: true)]
    private Collection $wishlistItems;

    #[ORM\Column]
    private ?DateTimeImmutable $createdAt;

    #[ORM\Column]
    private ?DateTimeImmutable $updatedAt;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[Groups(['wishlist:read', 'wishlist:update'])]
    private ?WishlistItem $currentlySavingFor = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    #[Groups(['wishlist:read', 'wishlist:update'])]
    private ?DateTimeImmutable $cantBuyBeforeDate = null;

    public function __construct()
    {
        $this->wishlistItems = new ArrayCollection();
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChild(): ?Child
    {
        return $this->child;
    }

    public function setChild(Child $child): static
    {
        $this->child = $child;

        return $this;
    }

    /**
     * @return Collection<int, WishlistItem>
     */
    public function getWishlistItems(): Collection
    {
        return $this->wishlistItems;
    }

    public function addWishlistItem(WishlistItem $wishlistItem): static
    {
        if (!$this->wishlistItems->contains($wishlistItem)) {
            $this->wishlistItems->add($wishlistItem);
            $wishlistItem->setWishlist($this);
        }

        return $this;
    }

    public function removeWishlistItem(WishlistItem $wishlistItem): static
    {
        if ($this->wishlistItems->removeElement($wishlistItem)) {
            // set the owning side to null (unless already changed)
            if ($wishlistItem->getWishlist() === $this) {
                $wishlistItem->setWishlist(null);
            }
        }

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

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getCurrentlySavingFor(): ?WishlistItem
    {
        return $this->currentlySavingFor;
    }

    public function setCurrentlySavingFor(?WishlistItem $currentlySavingFor): static
    {
        $this->currentlySavingFor = $currentlySavingFor;

        return $this;
    }

    public function getCantBuyBeforeDate(): ?DateTimeImmutable
    {
        return $this->cantBuyBeforeDate;
    }

    public function setCantBuyBeforeDate(?DateTimeImmutable $cantBuyBeforeDate): static
    {
        $this->cantBuyBeforeDate = $cantBuyBeforeDate;

        return $this;
    }
}
