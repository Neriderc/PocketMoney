<?php

namespace App\Tests\Api;

use App\Entity\Account;
use App\Entity\Child;
use App\Entity\Transaction;
use App\Repository\TransactionRepository;
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
}
