<?php

namespace App\Tests\Api;

use App\Entity\Child;
use App\Entity\Wishlist;
use App\Entity\WishlistItem;
use Symfony\Component\HttpFoundation\Response;

class WishlistApiTest extends BaseApiTestCase
{
    public function testGetWishlistsCollection(): void
    {
        $client = $this->createAuthenticatedClient();
        $child = $this->getRepository(Child::class)->findOneBy(['name' => 'Child 1']);

        $client->request('GET', '/api/children/' . $child->getId() . '/wishlists');

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@context' => '/api/contexts/Wishlist',
            '@type' => 'Collection',
            'totalItems' => 1,
        ]);
    }

    public function testUpdateWishlist(): void
    {
        $client = $this->createAuthenticatedClient();
        $child = $this->getRepository(Child::class)->findOneBy(['name' => 'Child 1']);
        $wishlistItem = $this->getRepository(WishlistItem::class)
            ->findOneBy(['description' => 'Wishlist Item 1']);
        $wishlist = $child->getWishlist();

        $client->request('PATCH', '/api/children/' . $child->getId() . '/wishlists/' . $wishlist->getId(), [
            'json' => [
                'currentlySavingFor' => '/api/wishlists/' . $wishlist->getId() . '/wishlist_items/' . $wishlistItem->getId(),
                'cantBuyBeforeDate' => '2025-05-20',
            ],
            'headers' => ['Content-Type' => 'application/merge-patch+json']
        ]);

        $this->assertResponseIsSuccessful();

        $updated = $this->getRepository(Wishlist::class)->find($wishlist->getId());

        $this->assertNotNull($updated);
        $this->assertEquals('2025-05-20', $updated->getCantBuyBeforeDate()->format('Y-m-d'));
        $this->assertEquals($wishlistItem->getId(), $updated->getCurrentlySavingFor()->getId());
    }

    public function testUnauthorizedAccess(): void
    {
        $client = static::createClient();
        $child = $this->getRepository(Child::class)->findOneBy(['name' => 'Child 1']);

        $client->request('GET', '/api/children/' . $child->getId() . '/wishlists');

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testForbiddenGetWishlistsCollection(): void
    {
        $child = $this->getFixtureReference('child_other_household', Child::class);

        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/children/' . $child->getId() . '/wishlists');

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testForbiddenUpdateWishlist(): void
    {
        $client = $this->createAuthenticatedClient();
        $child = $this->getFixtureReference('child_other_household', Child::class);
        $wishlistItem = $this->getFixtureReference('wishlist_item_other_household', WishlistItem::class);
        $wishlist = $this->getFixtureReference('wishlist_other_household', Wishlist::class);

        $client->request('PATCH', '/api/children/' . $child->getId() . '/wishlists/' . $wishlist->getId(), [
            'json' => [
                'currentlySavingFor' => '/api/wishlists/' . $wishlist->getId() . '/wishlist_items/' . $wishlistItem->getId(),
                'cantBuyBeforeDate' => '2025-05-20',
            ],
            'headers' => ['Content-Type' => 'application/merge-patch+json']
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testForbiddenUpdateWishlistOfOtherHouseholdWithMyItem(): void
    {
        $client = $this->createAuthenticatedClient();
        $child = $this->getRepository(Child::class)->findOneBy(['name' => 'Child 1']);
        $wishlist = $child->getWishlist();
        $wishlistItem = $this->getFixtureReference('wishlist_item_other_household', WishlistItem::class);



        $client->request('PATCH', '/api/children/' . $child->getId() . '/wishlists/' . $wishlist->getId(), [
            'json' => [
                'currentlySavingFor' => '/api/wishlists/' . $wishlist->getId() . '/wishlist_items/' . $wishlistItem->getId(),
                'cantBuyBeforeDate' => '2025-05-20',
            ],
            'headers' => ['Content-Type' => 'application/merge-patch+json']
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }
}