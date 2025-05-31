<?php

namespace App\Tests\Api;

use App\Entity\Child;
use App\Entity\WishlistItem;
use App\Repository\WishlistItemRepository;
use Symfony\Component\HttpFoundation\Response;

class WishlistItemApiTest extends BaseApiTestCase
{
    public function testGetWishlistItemsCollection(): void
    {
        $client = $this->createAuthenticatedClient();
        $child = $this->getRepository(Child::class)->findOneBy(['name' => 'Child 1']);
        $wishlist = $child->getWishlist();
        $client->request('GET', '/api/wishlists/' . $wishlist->getId() . '/wishlist_items');

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@context' => '/api/contexts/WishlistItem',
            '@type' => 'Collection',
            'totalItems' => 1,
        ]);
    }

    public function testGetWishlistItem(): void
    {
        $client = $this->createAuthenticatedClient();
        $child = $this->getRepository(Child::class)->findOneBy(['name' => 'Child 1']);
        $wishlist = $child->getWishlist();
        $wishlistItem = $wishlist->getWishlistItems()[0];

        $client->request('GET', '/api/wishlists/' . $wishlist->getId() . '/wishlist_items');

        $this->assertResponseIsSuccessful();

        $repo = static::getContainer()->get(WishlistItemRepository::class);
        $fetched = $repo->find($wishlistItem->getId());
        $this->assertNotNull($fetched);
        $this->assertEquals('Wishlist Item 1', $fetched->getDescription());
    }

    public function testCreateWishlistItem(): void
    {
        $client = $this->createAuthenticatedClient();
        $child = $this->getRepository(Child::class)->findOneBy(['name' => 'Child 1']);
        $wishlist = $child->getWishlist();

        $client->request('POST', '/api/wishlists/' . $wishlist->getId() . '/wishlist_items', [
            'json' => [
                'description' => 'New Wishlist Item',
                'amount' => 1000,
                'priority' => 100,
            ],
            'headers' => ['Content-Type' => 'application/ld+json']
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $repo = static::getContainer()->get(WishlistItemRepository::class);
        $wishlistItem = $repo->findOneBy(['description' => 'New Wishlist Item', 'amount' => 1000, 'priority'  => 100]);

        $this->assertNotNull($wishlistItem);
    }

    public function testUpdateWishlistItem(): void
    {
        $client = $this->createAuthenticatedClient();
        $wishlistItem = $this->getRepository(WishlistItem::class)
            ->findOneBy(['description' => 'Wishlist Item 1']);

        $client->request('PATCH', '/api/wishlists/' . $wishlistItem->getWishlist()->getId() . '/wishlist_items/' . $wishlistItem->getId(), [
            'json' => [
                'description' => 'Updated Description',
                'amount' => 1500,
                'priority' => 150,
            ],
            'headers' => ['Content-Type' => 'application/merge-patch+json']
        ]);

        $this->assertResponseIsSuccessful();

        $updated = $this->getRepository(WishlistItem::class)->find($wishlistItem->getId());

        $this->assertNotNull($updated);
        $this->assertEquals('Updated Description', $updated->getDescription());
        $this->assertEquals(1500, $updated->getAmount());
        $this->assertEquals(150, $updated->getPriority());
    }

    public function testDeleteWishlistItem(): void
    {
        $client = $this->createAuthenticatedClient();
        $wishlistItem = $this->getRepository(WishlistItem::class)
            ->findOneBy(['description' => 'Wishlist Item 1']);
        $id = $wishlistItem->getId();

        $client->request('DELETE', '/api/wishlists/' . $wishlistItem->getWishlist()->getId() . '/wishlist_items/' . $id);
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $this->assertNull($this->getRepository(WishlistItem::class)->find($id));
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

    public function testForbiddenGetWishlistItemsCollection(): void
    {
        $client = $this->createAuthenticatedClient();
        $child = $this->getFixtureReference('child_other_household', Child::class);
        $wishlist = $child->getWishlist();
        $client->request('GET', '/api/wishlists/' . $wishlist->getId() . '/wishlist_items');

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testForbiddenGetWishlistItem(): void
    {
        $client = $this->createAuthenticatedClient();
        $child = $this->getFixtureReference('child_other_household', Child::class);
        $wishlist = $child->getWishlist();
        $wishlistItem = $wishlist->getWishlistItems()[0];

        $client->request('GET', '/api/wishlists/' . $wishlist->getId() . '/wishlist_items');

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testForbiddenCreateWishlistItem(): void
    {
        $client = $this->createAuthenticatedClient();
        $child = $this->getFixtureReference('child_other_household', Child::class);
        $wishlist = $child->getWishlist();

        $client->request('POST', '/api/wishlists/' . $wishlist->getId() . '/wishlist_items', [
            'json' => [
                'description' => 'New Wishlist Item',
                'amount' => 1000,
                'priority' => 100,
            ],
            'headers' => ['Content-Type' => 'application/ld+json']
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testForbiddenUpdateWishlistItem(): void
    {
        $client = $this->createAuthenticatedClient();
        $wishlistItem = $this->getFixtureReference('wishlist_item_other_household', WishlistItem::class);

        $client->request('PATCH', '/api/wishlists/' . $wishlistItem->getWishlist()->getId() . '/wishlist_items/' . $wishlistItem->getId(), [
            'json' => [
                'description' => 'Updated Description',
                'amount' => 1500,
                'priority' => 150,
            ],
            'headers' => ['Content-Type' => 'application/merge-patch+json']
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testForbiddenDeleteWishlistItem(): void
    {
        $client = $this->createAuthenticatedClient();
        $wishlistItem = $this->getFixtureReference('wishlist_item_other_household', WishlistItem::class);
        $id = $wishlistItem->getId();

        $client->request('DELETE', '/api/wishlists/' . $wishlistItem->getWishlist()->getId() . '/wishlist_items/' . $id);
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
}
