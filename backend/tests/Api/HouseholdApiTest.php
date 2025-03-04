<?php

namespace App\Tests\Api;

use App\Entity\Household;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;

class HouseholdApiTest extends BaseApiTestCase
{
    public function testGetHouseholdCollection(): void
    {
        $client = $this->createAuthenticatedClient();

        $response = $client->request('GET', '/api/households');

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@context' => '/api/contexts/Household',
            '@type' => 'Collection',
        ]);

        $data = $response->toArray();
        $this->assertArrayHasKey('member', $data);
        $this->assertGreaterThan(0, count($data['member']));
    }

    public function testGetHouseholdItem(): void
    {
        $client = $this->createAuthenticatedClient();
        $household = $this->getRepository(Household::class)
            ->findOneBy(['name' => 'Household 1']);

        $response = $client->request('GET', '/api/households/' . $household->getId());

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@type' => 'Household',
            'name' => $household->getName(),
        ]);
    }

    public function testUpdateHousehold(): void
    {
        $client = $this->createAuthenticatedClient();
        $household = $this->getRepository(Household::class)
            ->findOneBy(['name' => 'Household 1']);

        $response = $client->request('PATCH', '/api/households/' . $household->getId(), [
            'json' => [
                'name' => 'Updated Household Name',
            ],
            'headers' => [
                'Content-Type' => 'application/merge-patch+json'
            ]
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            'name' => 'Updated Household Name',
        ]);

        $updated = $this->getRepository(Household::class)->find($household->getId());
        $this->assertEquals('Updated Household Name', $updated->getName());
    }

    public function testDeleteHousehold(): void
    {
        $client = $this->createAuthenticatedClient();
        $household = $this->getRepository(Household::class)
            ->findOneBy(['name' => 'Household 1']);

        $response = $client->request('DELETE', '/api/households/' . $household->getId());

        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        $this->assertNull($this->getRepository(Household::class)->find($household->getId()));
    }

    public function testUnauthorizedAccess(): void
    {
        $client = static::createClient();
        $household = $this->getRepository(Household::class)
            ->findOneBy(['name' => 'Household 1']);

        $client->request('GET', '/api/households/' . $household->getId());
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testForbiddenAccess(): void
    {
        $user = $this->getFixtureReference('user', User::class);
        $household = $this->getFixtureReference('household2', Household::class);

        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/households/' . $household->getId());
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
}
