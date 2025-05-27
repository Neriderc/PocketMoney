<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\WishlistRepository;
use App\State\WishlistCollectionProvider;
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
}
