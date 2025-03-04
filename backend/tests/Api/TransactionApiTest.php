<?php

namespace App\Tests\Api;

use App\Entity\Account;
use App\Entity\Transaction;
use App\Repository\TransactionRepository;
use Symfony\Component\HttpFoundation\Response;

class TransactionApiTest extends BaseApiTestCase
{
    public function testGetTransactionsCollection(): void
    {
        $client = $this->createAuthenticatedClient();
        $account = $this->getRepository(Account::class)->findOneBy(['name' => 'Account 1']);

        $client->request('GET', '/api/accounts/' . $account->getId() . '/transactions');

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@context' => '/api/contexts/Transaction',
            '@type' => 'Collection',
        ]);
    }

    public function testGetTransactionItem(): void
    {
        $client = $this->createAuthenticatedClient();
        $transaction = $this->getRepository(Transaction::class)
            ->findOneBy(['description' => 'Transaction 1']);

        $client->request('GET', '/api/accounts/' . $transaction->getAccount()->getId() . '/transactions/' . $transaction->getId());

        $this->assertResponseIsSuccessful();

        $repo = static::getContainer()->get(TransactionRepository::class);
        $fetched = $repo->find($transaction->getId());
        $this->assertNotNull($fetched);
        $this->assertEquals('Transaction 1', $fetched->getDescription());
    }

    public function testCreateTransaction(): void
    {
        $client = $this->createAuthenticatedClient();
        $account = $this->getRepository(Account::class)->findOneBy(['name' => 'Account 1']);

        $client->request('POST', '/api/accounts/' . $account->getId() . '/transactions', [
            'json' => [
                'description' => 'New Transaction',
                'amount' => 1000,
                'transactionDate' => '2025-04-20',
                'comment' => 'Test Comment',
                'account' => '/api/children/' . $account->getChild()->getId() . '/accounts/' . $account->getId(),
            ],
            'headers' => ['Content-Type' => 'application/ld+json']
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $repo = static::getContainer()->get(TransactionRepository::class);
        $transaction = $repo->findOneBy(['description' => 'New Transaction', 'amount' => 1000]);

        $this->assertNotNull($transaction);
        $this->assertEquals('2025-04-20', $transaction->getTransactionDate()->format('Y-m-d'));
        $this->assertEquals('Test Comment', $transaction->getComment());
    }


    public function testUpdateTransaction(): void
    {
        $client = $this->createAuthenticatedClient();
        $transaction = $this->getRepository(Transaction::class)
            ->findOneBy(['description' => 'Transaction 1']);

        $client->request('PATCH', '/api/accounts/' . $transaction->getAccount()->getId() . '/transactions/' . $transaction->getId(), [
            'json' => [
                'description' => 'Updated Description',
                'amount' => 1500,
                'transactionDate' => '2025-04-21',
                'comment' => 'Updated Comment',
            ],
            'headers' => ['Content-Type' => 'application/merge-patch+json']
        ]);

        $this->assertResponseIsSuccessful();

        $updated = $this->getRepository(Transaction::class)->find($transaction->getId());

        $this->assertNotNull($updated);
        $this->assertEquals('Updated Description', $updated->getDescription());
        $this->assertEquals(1500, $updated->getAmount());
        $this->assertEquals('2025-04-21', $updated->getTransactionDate()->format('Y-m-d'));
        $this->assertEquals('Updated Comment', $updated->getComment());
    }

    public function testDeleteTransaction(): void
    {
        $client = $this->createAuthenticatedClient();
        $transaction = $this->getRepository(Transaction::class)
            ->findOneBy(['description' => 'Transaction 1']);
        $id = $transaction->getId();

        $client->request('DELETE', '/api/accounts/' . $transaction->getAccount()->getId() . '/transactions/' . $id);
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $this->assertNull($this->getRepository(Transaction::class)->find($id));
    }

    public function testUnauthorizedAccess(): void
    {
        $client = static::createClient();
        $transaction = $this->getRepository(Transaction::class)
            ->findOneBy(['description' => 'Transaction 1']);

        $client->request('GET', '/api/accounts/' . $transaction->getAccount()->getId() . '/transactions/' . $transaction->getId());

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testForbiddenAccessToTransaction(): void
    {
        $transaction = $this->getFixtureReference('transaction_other_household', Transaction::class);

        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/accounts/' . $transaction->getAccount()->getId() . '/transactions/' . $transaction->getId());

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
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

    public function testForbiddenCreateTransaction(): void
    {
        $transaction = $this->getFixtureReference('transaction_other_household', Transaction::class);
        $account = $transaction->getAccount();

        $client = $this->createAuthenticatedClient();
        $client->request('POST', '/api/accounts/' . $account->getId() . '/transactions', [
            'json' => [
                'description' => 'New Transaction',
                'amount' => 1000,
                'transactionDate' => '2025-04-20',
                'comment' => 'Test Comment',
                'account' => '/api/children/' . $account->getChild()->getId() . '/accounts/' . $account->getId(),
            ],
            'headers' => ['Content-Type' => 'application/ld+json']
        ]);

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

    public function testForbiddenDeleteTransaction(): void
    {
        $transaction = $this->getFixtureReference('transaction_other_household', Transaction::class);

        $client = $this->createAuthenticatedClient();
        $client->request('DELETE', '/api/accounts/' . $transaction->getAccount()->getId() . '/transactions/' . $transaction->getId());

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
}
