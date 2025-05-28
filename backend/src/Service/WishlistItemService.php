<?php

namespace App\Service;

use App\Entity\Wishlist;
use App\Entity\WishlistItem;
use Doctrine\ORM\EntityManagerInterface;

class WishlistItemService
{
    private EntityManagerInterface $entityManager;
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function addWishlistItemToWishlist(Wishlist $wishlist, array $data): WishlistItem
    {
        $wishlistItem = new WishlistItem();
        $wishlistItem->setAmount($data["amount"]);
        $wishlistItem->setDescription($data["description"]);
        $wishlistItem->setPriority($data["priority"]);
        $wishlistItem->setWishlist($wishlist);
        $wishlist->addWishlistItem($wishlistItem);

        $this->entityManager->persist($wishlistItem);
        $this->entityManager->persist($wishlist);
        $this->entityManager->flush();

        return $wishlistItem;
    }
}
