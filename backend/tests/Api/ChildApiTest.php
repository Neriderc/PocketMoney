<?php

namespace App\Tests\Api;

use App\Entity\Account;
use App\Entity\Child;
use App\Entity\Household;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;

class ChildApiTest extends BaseApiTestCase
{
    public function testGetChildrenCollection(): void
    {
        $client = $this->createAuthenticatedClient();
        $household = $this->getRepository(Household::class)
            ->findOneBy(['name' => 'Household 1']);

        $response = $client->request('GET', '/api/households/'.$household->getId().'/children');

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@context' => '/api/contexts/Child',
            '@id' => '/api/households/'.$household->getId().'/children',
            '@type' => 'Collection',
            'totalItems' => 3,
        ]);

        $data = $response->toArray();
        foreach ($data['member'] as $child) {
            $this->assertMatchesRegularExpression('/^Child \d+$/', $child['name']);
        }
    }

    public function testGetChildItem(): void
    {
        $client = $this->createAuthenticatedClient();
        $child = $this->getRepository(Child::class)
            ->findOneBy(['name' => 'Child 1']);

        $response = $client->request('GET', '/api/children/'.$child->getId());

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@type' => 'Child',
            'name' => 'Child 1',
        ]);
    }

    public function testCreateChild(): void
    {
        $client = $this->createAuthenticatedClient();
        $household = $this->getRepository(Household::class)
            ->findOneBy(['name' => 'Household 1']);

        $response = $client->request('POST', '/api/households/'.$household->getId().'/children', [
            'json' => [
                'name' => 'New Child',
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertJsonContains([
            'name' => 'New Child',
        ]);

        $this->assertEntityExists(Child::class, ['name' => 'New Child']);
    }

    public function testUpdateChild(): void
    {
        $client = $this->createAuthenticatedClient();
        $child = $this->getRepository(Child::class)
            ->findOneBy(['name' => 'Child 1']);

        $response = $client->request('PATCH', '/api/children/'.$child->getId(), [
            'json' => [
                'name' => 'Updated Name',
            ],
            'headers' => [
                'Content-Type' => 'application/merge-patch+json'
            ]
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            'name' => 'Updated Name',
        ]);

        $updatedChild = $this->getRepository(Child::class)->find($child->getId());
        $this->assertEquals('Updated Name', $updatedChild->getName());
    }

    public function testDeleteChild(): void
    {
        $client = $this->createAuthenticatedClient();
        $child = $this->getRepository(Child::class)
            ->findOneBy(['name' => 'Child 2']);
        $childId = $child->getId();

        $response = $client->request('DELETE', '/api/children/'.$childId);
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $this->assertNull(
            $this->getRepository(Child::class)->find($childId)
        );
    }

    public function testDeleteAccount(): void
    {
        $client = $this->createAuthenticatedClient();
        $child = $this->getRepository(Child::class)
            ->findOneBy(['name' => 'Child 1']);
        $account = $this->getRepository(Account::class)
            ->findOneBy([
                'child' => $child,
                'name' => 'Account 1'
            ]);

        $response = $client->request('DELETE', '/api/children/'.$child->getId().'/accounts/'.$account->getId());
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $this->assertNull(
            $this->getRepository(Account::class)->find($account->getId())
        );
    }

    public function testAdminAccess(): void
    {
        $client = $this->createAuthenticatedClient($this->adminAuthToken);
        $child = $this->getRepository(Child::class)
            ->findOneBy(['name' => 'Child 1']);

        $response = $client->request('GET', '/api/children/'.$child->getId());
        $this->assertResponseIsSuccessful();
    }

    public function testUnauthorizedAccess(): void
    {
        $client = static::createClient();
        $child = $this->getRepository(Child::class)
            ->findOneBy(['name' => 'Child 1']);

        $response = $client->request('GET', '/api/children/'.$child->getId());
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testForbiddenAccess(): void
    {
        $user = $this->getFixtureReference('user', User::class);
        $household2 = $this->getFixtureReference('household2', Household::class);

        $childFromOtherHousehold = $this->getRepository(Child::class)
            ->findOneBy([
                'household' => $household2,
                'name' => 'Child 1'
            ]);

        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/children/'.$childFromOtherHousehold->getId());

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testPatchChildForbidden(): void
    {
        $household2 = $this->getFixtureReference('household2', Household::class);
        $child = $this->getRepository(Child::class)->findOneBy([
            'household' => $household2,
            'name' => 'Child 1'
        ]);

        $client = $this->createAuthenticatedClient();
        $client->request('PATCH', '/api/children/'.$child->getId(), [
            'json' => ['name' => 'Should Not Update'],
            'headers' => ['Content-Type' => 'application/merge-patch+json']
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testDeleteAccountForbidden(): void
    {
        $household2 = $this->getFixtureReference('household2', Household::class);
        $child = $this->getRepository(Child::class)->findOneBy([
            'household' => $household2,
            'name' => 'Child 1'
        ]);
        $account = $this->getRepository(Account::class)->findOneBy([
            'child' => $child,
            'name' => 'Account 1'
        ]);

        $client = $this->createAuthenticatedClient();
        $client->request('DELETE', '/api/children/'.$child->getId().'/accounts/'.$account->getId());

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testGetChildrenCollectionForbidden(): void
    {
        $household2 = $this->getFixtureReference('household2', Household::class);
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/households/'.$household2->getId().'/children');

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testPostChildForbidden(): void
    {
        $household2 = $this->getFixtureReference('household2', Household::class);
        $client = $this->createAuthenticatedClient();

        $client->request('POST', '/api/households/'.$household2->getId().'/children', [
            'json' => ['name' => 'Forbidden Child'],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testDeleteChildForbidden(): void
    {
        $household2 = $this->getFixtureReference('household2', Household::class);
        $child = $this->getRepository(Child::class)->findOneBy([
            'household' => $household2,
            'name' => 'Child 1'
        ]);

        $client = $this->createAuthenticatedClient();
        $client->request('DELETE', '/api/children/'.$child->getId());

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

}
