<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\WishlistRepository;
use App\State\WishlistCollectionProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\Link;

#[ORM\Entity(repositoryClass: WishlistRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/children/{childId}/wishlists',
            uriVariables: [
                'childId' => new Link(toProperty: 'child', fromClass: Child::class),
            ],
            provider: WishlistCollectionProvider::class
        ),
    ]
)]
class Wishlist
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
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
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->wishlistItems = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
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
}
