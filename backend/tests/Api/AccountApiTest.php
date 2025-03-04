<?php

namespace App\Tests\Api;

use App\Entity\Account;
use App\Entity\Child;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;

class AccountApiTest extends BaseApiTestCase
{
    public function testCreateAccount(): void
    {
        $client = $this->createAuthenticatedClient();
        $child = $this->getRepository(Child::class)
            ->findOneBy(['name' => 'Child 1']);


        $response = $client->request('POST', '/api/children/'.$child->getId().'/accounts', [
            'json' => [
                'name' => 'New Account',
                'icon' => 'bi-123',
                'color' => '#bedadc',
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertJsonContains([
            'name' => 'New Account',
            'icon' => 'bi-123',
            'color' => '#bedadc',
        ]);

        $data = $response->toArray();
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('balance', $data);
    }

    public function testGetAccountsCollection(): void
    {
        $client = $this->createAuthenticatedClient();
        $child = $this->getRepository(Child::class)
            ->findOneBy(['name' => 'Child 1']);

        $response = $client->request('GET', '/api/children/' . $child->getId() . '/accounts');

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@context' => '/api/contexts/Child',
            '@type' => 'Collection',
        ]);

        $data = $response->toArray();

        $this->assertArrayHasKey('member', $data);
        $this->assertGreaterThan(0, count($data['member']));

        $childData = $data['member'][0];
        $this->assertArrayHasKey('accounts', $childData);
        $this->assertGreaterThan(0, count($childData['accounts']));
    }



    public function testGetAccountItem(): void
    {
        $client = $this->createAuthenticatedClient();
        $child = $this->getRepository(Child::class)
            ->findOneBy(['name' => 'Child 1']);
        $account = $this->getRepository(Account::class)
            ->findOneBy(['child' => $child]);

        $response = $client->request('GET', '/api/children/'.$child->getId().'/accounts/'.$account->getId());

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@type' => 'Account',
            'name' => $account->getName(),
        ]);
    }

    public function testUpdateAccount(): void
    {
        $client = $this->createAuthenticatedClient();
        $child = $this->getRepository(Child::class)
            ->findOneBy(['name' => 'Child 1']);
        $account = $this->getRepository(Account::class)
            ->findOneBy(['child' => $child]);

        $response = $client->request('PATCH', '/api/children/'.$child->getId().'/accounts/'.$account->getId(), [
            'json' => [
                'name' => 'Updated Account Name',
            ],
            'headers' => [
                'Content-Type' => 'application/merge-patch+json'
            ]
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            'name' => 'Updated Account Name',
        ]);

        $updated = $this->getRepository(Account::class)->find($account->getId());
        $this->assertEquals('Updated Account Name', $updated->getName());
    }

    public function testDeleteAccount(): void
    {
        $client = $this->createAuthenticatedClient();
        $child = $this->getRepository(Child::class)
            ->findOneBy(['name' => 'Child 1']);
        $account = $this->getRepository(Account::class)
            ->findOneBy(['child' => $child]);

        $response = $client->request('DELETE', '/api/children/'.$child->getId().'/accounts/'.$account->getId());

        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        $this->assertNull($this->getRepository(Account::class)->find($account->getId()));
    }

    public function testUnauthorizedAccess(): void
    {
        $client = static::createClient();
        $child = $this->getRepository(Child::class)
            ->findOneBy(['name' => 'Child 1']);
        $account = $this->getRepository(Account::class)
            ->findOneBy(['child' => $child]);

        $client->request('GET', '/api/children/'.$child->getId().'/accounts/'.$account->getId());
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testGetAccountsCollectionForbidden(): void
    {
        $client = $this->createAuthenticatedClient();
        $child = $this->getFixtureReference('child_other_household', Child::class);

        $client->request('GET', '/api/children/' . $child->getId() . '/accounts');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testCreateAccountForbidden(): void
    {
        $client = $this->createAuthenticatedClient();
        $child = $this->getFixtureReference('child_other_household', Child::class);


        $response = $client->request('POST', '/api/children/'.$child->getId().'/accounts', [
            'json' => [
                'name' => 'New Account',
                'icon' => 'bi-123',
                'color' => '#bedadc',
            ],
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testGetAccountItemForbidden(): void
    {
        $client = $this->createAuthenticatedClient();
        $child = $this->getFixtureReference('child_other_household', Child::class);
        $account = $this->getRepository(Account::class)
            ->findOneBy(['child' => $child]);

        $client->request('GET', '/api/children/'.$child->getId().'/accounts/'.$account->getId());
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testPatchAccountForbidden(): void
    {
        $client = $this->createAuthenticatedClient();
        $child = $this->getFixtureReference('child_other_household', Child::class);
        $account = $this->getRepository(Account::class)
            ->findOneBy(['child' => $child]);

        $client->request('PATCH', '/api/children/'.$child->getId().'/accounts/'.$account->getId(), [
            'json' => ['name' => 'Invalid Update'],
            'headers' => ['Content-Type' => 'application/merge-patch+json']
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testDeleteAccountForbidden(): void
    {
        $client = $this->createAuthenticatedClient();
        $child = $this->getFixtureReference('child_other_household', Child::class);
        $account = $this->getRepository(Account::class)
            ->findOneBy(['child' => $child]);

        $client->request('DELETE', '/api/children/'.$child->getId().'/accounts/'.$account->getId());
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

}
