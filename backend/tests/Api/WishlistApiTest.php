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
            '@context' => '/api/contexts/ScheduledTransaction',
            '@type' => 'Collection',
        ]);
    }

    public function testUnauthorizedAccess(): void
    {
        $client = static::createClient();
        $transaction = $this->getRepository(Transaction::class)
            ->findOneBy(['description' => 'Transaction 1']);

        $client->request('GET', '/api/accounts/' . $transaction->getAccount()->getId() . '/transactions/' . $transaction->getId());

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testLinkedUserCanViewTransaction(): void
    {
        $account = $this->getRepository(Account::class)->findOneBy(['name' => 'Account 1']);
        $transaction = $this->getRepository(Transaction::class)
            ->findOneBy(['account' => $account]);

        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/accounts/' . $account->getId() . '/transactions/' . $transaction->getId());

        $this->assertResponseIsSuccessful();

        $repo = static::getContainer()->get(TransactionRepository::class);
        $fetched = $repo->find($transaction->getId());
        $this->assertNotNull($fetched);
        $this->assertEquals($account->getId(), $fetched->getAccount()->getId());
    }

    public function testForbiddenGetTransactionsCollection(): void
    {
        $transaction = $this->getFixtureReference('transaction_other_household', Transaction::class);
        $account = $transaction->getAccount();

        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/accounts/' . $account->getId() . '/transactions');

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testForbiddenUpdateTransaction(): void
    {
        $transaction = $this->getFixtureReference('transaction_other_household', Transaction::class);

        $client = $this->createAuthenticatedClient();
        $client->request('PATCH', '/api/accounts/' . $transaction->getAccount()->getId() . '/transactions/' . $transaction->getId(), [
            'json' => ['description' => 'Illegal update'],
            'headers' => ['Content-Type' => 'application/merge-patch+json']
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
}
