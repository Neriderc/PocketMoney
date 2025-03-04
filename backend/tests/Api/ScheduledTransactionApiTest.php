<?php

namespace App\Tests\Api;

use App\Entity\Child;
use App\Entity\ScheduledTransaction;
use App\Entity\User;
use App\Enum\AmountBase;
use App\Enum\RepeatFrequency;
use App\Repository\ScheduledTransactionRepository;
use Symfony\Component\HttpFoundation\Response;

class ScheduledTransactionApiTest extends BaseApiTestCase
{
    public function testGetScheduledTransactionsCollection(): void
    {
        $client = $this->createAuthenticatedClient();
        $child = $this->getRepository(Child::class)->findOneBy(['name' => 'Child 1']);

        $client->request('GET', '/api/children/' . $child->getId() . '/scheduled_transactions');

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@context' => '/api/contexts/ScheduledTransaction',
            '@type' => 'Collection',
        ]);
    }

    public function testGetScheduledTransactionItem(): void
    {
        $client = $this->createAuthenticatedClient();
        $schedule = $this->getRepository(ScheduledTransaction::class)
            ->findOneBy(['description' => 'Test Schedule 1']);

        $client->request('GET', '/api/children/' . $schedule->getChild()->getId() . '/scheduled_transactions/' . $schedule->getId());

        $this->assertResponseIsSuccessful();

        $repo = static::getContainer()->get(ScheduledTransactionRepository::class);
        $fetched = $repo->find($schedule->getId());
        $this->assertNotNull($fetched);
        $this->assertEquals('Test Schedule 1', $fetched->getDescription());
    }

    public function testCreateScheduledTransaction(): void
    {
        $client = $this->createAuthenticatedClient();
        $child = $this->getRepository(Child::class)->findOneBy(['name' => 'Child 1']);

        $accounts = array_map(function ($account) use ($child) {
            return '/api/children/' . $child->getId() . '/accounts/' . $account->getId();
        }, $child->getAccounts()->toArray());

        $client->request('POST', '/api/children/' . $child->getId() . '/scheduled_transactions', [
            'json' => [
                'description' => 'New Schedule',
                'amount' => 500,
                'repeatFrequency' => 'weekly',
                'nextExecutionDate' => '2020-12-31',
                'comment' => 'Test Comment',
                'amountCalculation' => 'fixed',
                'accounts' => $accounts,
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $repo = static::getContainer()->get(ScheduledTransactionRepository::class);
        $scheduled = $repo->findOneBy([
            'description' => 'New Schedule',
            'amount' => 500,
            'repeatFrequency' => 'weekly',
            'comment' => 'Test Comment',
            'amountBase' => 'fixed'
        ]);

        $this->assertNotNull($scheduled);
        $this->assertEquals('2020-12-31', $scheduled->getNextExecutionDate()->format('Y-m-d'));
        $this->assertEquals(RepeatFrequency::WEEKLY, $scheduled->getRepeatFrequency());
        $this->assertEquals('Test Comment', $scheduled->getComment());
        $this->assertEquals(AmountBase::FIXED, $scheduled->getAmountBase());
    }

    public function testUpdateScheduledTransaction(): void
    {
        $client = $this->createAuthenticatedClient();
        $schedule = $this->getRepository(ScheduledTransaction::class)
            ->findOneBy(['description' => 'Test Schedule 1']);

        $account = $schedule->getAccounts()->first();
        $this->assertNotFalse($account, 'ScheduledTransaction must have at least one linked account for this test.');

        $client->request('PATCH', '/api/children/' . $schedule->getChild()->getId() . '/scheduled_transactions/' . $schedule->getId(), [
            'json' => [
                'description' => 'Updated Description',
                'amount' => 500,
                'nextExecutionDate' => '2020-12-31T00:00:00+00:00',
                'amountBase' => 'fixed',
                'repeatFrequency' => 'daily',
                'accounts' => [
                    '/api/children/' . $schedule->getChild()->getId() . '/accounts/' . $account->getId(),
                ]
            ],
            'headers' => ['Content-Type' => 'application/merge-patch+json']
        ]);

        $this->assertResponseIsSuccessful();

        $updated = $this->getRepository(ScheduledTransaction::class)->find($schedule->getId());

        $this->assertNotNull($updated);
        $this->assertEquals('Updated Description', $updated->getDescription());
        $this->assertEquals(500, $updated->getAmount());
        $this->assertEquals('2020-12-31', $updated->getNextExecutionDate()->format('Y-m-d'));
        $this->assertEquals('fixed', $updated->getAmountBase()->value);
        $this->assertEquals('daily', $updated->getRepeatFrequency()->value);
        $this->assertCount(1, $updated->getAccounts());
        $this->assertEquals($account->getId(), $updated->getAccounts()->first()->getId());
    }


    public function testDeleteScheduledTransaction(): void
    {
        $client = $this->createAuthenticatedClient();
        $schedule = $this->getRepository(ScheduledTransaction::class)
            ->findOneBy(['description' => 'Test Schedule 1']);
        $id = $schedule->getId();

        $client->request('DELETE', '/api/children/' . $schedule->getChild()->getId() . '/scheduled_transactions/' . $id);
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $this->assertNull($this->getRepository(ScheduledTransaction::class)->find($id));
    }

    public function testUnauthorizedAccess(): void
    {
        $client = static::createClient();
        $schedule = $this->getRepository(ScheduledTransaction::class)
            ->findOneBy(['description' => 'Test Schedule 1']);

        $client->request('GET', '/api/children/' . $schedule->getChild()->getId() . '/scheduled_transactions/' . $schedule->getId());

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testForbiddenAccessToSchedule(): void
    {
        $user = $this->getFixtureReference('user', User::class);
        $schedule = $this->getFixtureReference('scheduled_transaction_other_household', ScheduledTransaction::class);

        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/children/' . $schedule->getChild()->getId() . '/scheduled_transactions/' . $schedule->getId());

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testLinkedUserCanViewSchedule(): void
    {
        $child = $this->getRepository(Child::class)->findOneBy(['name' => 'Child 1']);
        $linkedUser = $child->getLinkedUser();

        $schedule = $this->getRepository(ScheduledTransaction::class)
            ->findOneBy(['child' => $child]);

        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/children/' . $child->getId() . '/scheduled_transactions/' . $schedule->getId());

        $this->assertResponseIsSuccessful();

        $repo = static::getContainer()->get(ScheduledTransactionRepository::class);
        $fetched = $repo->find($schedule->getId());
        $this->assertNotNull($fetched);
        $this->assertEquals($child->getId(), $fetched->getChild()->getId());
    }

    public function testForbiddenGetScheduledTransactionsCollection(): void
    {
        $schedule = $this->getFixtureReference('scheduled_transaction_other_household', ScheduledTransaction::class);
        $child = $schedule->getChild();

        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/children/' . $child->getId() . '/scheduled_transactions');

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testForbiddenCreateScheduledTransaction(): void
    {
        $schedule = $this->getFixtureReference('scheduled_transaction_other_household', ScheduledTransaction::class);
        $child = $schedule->getChild();

        $accounts = array_map(function ($account) use ($child) {
            return '/api/children/' . $child->getId() . '/accounts/' . $account->getId();
        }, $child->getAccounts()->toArray());

        $client = $this->createAuthenticatedClient();
        $client->request('POST', '/api/children/' . $child->getId() . '/scheduled_transactions', [
            'json' => [
                'description' => 'Should Not Work',
                'amount' => 500,
                'repeatFrequency' => 'weekly',
                'nextExecutionDate' => '2020-12-31',
                'comment' => 'Invalid Create',
                'amountCalculation' => 'fixed',
                'accounts' => $accounts,
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testForbiddenUpdateScheduledTransaction(): void
    {
        $schedule = $this->getFixtureReference('scheduled_transaction_other_household', ScheduledTransaction::class);

        $client = $this->createAuthenticatedClient();
        $client->request('PATCH', '/api/children/' . $schedule->getChild()->getId() . '/scheduled_transactions/' . $schedule->getId(), [
            'json' => ['description' => 'Illegal update'],
            'headers' => ['Content-Type' => 'application/merge-patch+json']
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }


    public function testForbiddenDeleteScheduledTransaction(): void
    {
        $schedule = $this->getFixtureReference('scheduled_transaction_other_household', ScheduledTransaction::class);

        $client = $this->createAuthenticatedClient();
        $client->request('DELETE', '/api/children/' . $schedule->getChild()->getId() . '/scheduled_transactions/' . $schedule->getId());

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

}
