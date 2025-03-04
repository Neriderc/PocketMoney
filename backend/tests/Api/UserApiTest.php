<?php

namespace App\Tests\Api;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;

class UserApiTest extends BaseApiTestCase
{
    public function testGetUsersCollection(): void
    {
        $client = $this->createAuthenticatedClient($this->adminAuthToken);

        $client->request('GET', '/api/users');

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@context' => '/api/contexts/User',
            '@type' => 'Collection',
        ]);
    }

    public function testGetUserItem(): void
    {
        $client = $this->createAuthenticatedClient($this->adminAuthToken);
        $user = $this->getRepository(User::class)->findOneBy(['username' => 'test']);

        $client->request('GET', '/api/users/' . $user->getId());

        $this->assertResponseIsSuccessful();

        $repo = static::getContainer()->get(UserRepository::class);
        $fetched = $repo->find($user->getId());
        $this->assertNotNull($fetched);
        $this->assertEquals('test', $fetched->getUsername());
    }

    public function testCreateUser(): void
    {
        $client = $this->createAuthenticatedClient($this->adminAuthToken);

        $client->request('POST', '/api/users', [
            'json' => [
                'username' => 'newuser',
                'password' => 'password123',
                'roles' => ['ROLE_USER'],
                'email' => 'newuser@example.com',
            ],
            'headers' => ['Content-Type' => 'application/ld+json']
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $repo = static::getContainer()->get(UserRepository::class);
        $user = $repo->findOneBy(['username' => 'newuser']);

        $this->assertNotNull($user);
        $this->assertEquals('newuser@example.com', $user->getEmail());
    }

    public function testUpdateUser(): void
    {
        $client = $this->createAuthenticatedClient($this->adminAuthToken);
        $user = $this->getRepository(User::class)->findOneBy(['username' => 'test']);

        $client->request('PATCH', '/api/users/' . $user->getId(), [
            'json' => [
                'username' => 'updateduser',
                'email' => 'updateduser@example.com',
            ],
            'headers' => ['Content-Type' => 'application/merge-patch+json']
        ]);

        $this->assertResponseIsSuccessful();

        $updated = $this->getRepository(User::class)->find($user->getId());

        $this->assertNotNull($updated);
        $this->assertEquals('updateduser', $updated->getUsername());
        $this->assertEquals('updateduser@example.com', $updated->getEmail());
    }

    public function testDeleteUser(): void
    {
        $client = $this->createAuthenticatedClient($this->adminAuthToken);
        $user = $this->getRepository(User::class)->findOneBy(['username' => 'test']);
        $id = $user->getId();

        $client->request('DELETE', '/api/users/' . $id);
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $this->assertNull($this->getRepository(User::class)->find($id));
    }

    public function testUnauthorizedAccess(): void
    {
        $client = static::createClient();
        $user = $this->getRepository(User::class)->findOneBy(['username' => 'test']);

        $client->request('GET', '/api/users/' . $user->getId());

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testForbiddenAccessToUser(): void
    {
        $client = $this->createAuthenticatedClient();
        $user = $this->getRepository(User::class)->findOneBy(['username' => 'test']);

        $client->request('GET', '/api/users/' . $user->getId());

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testAdminCanViewUser(): void
    {
        $client = $this->createAuthenticatedClient($this->adminAuthToken);
        $user = $this->getRepository(User::class)->findOneBy(['username' => 'test']);

        $client->request('GET', '/api/users/' . $user->getId());

        $this->assertResponseIsSuccessful();
    }

    public function testForbiddenGetUsersCollection(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/users');

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testForbiddenCreateUser(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request('POST', '/api/users', [
            'json' => [
                'username' => 'forbiddenuser',
                'password' => 'password123',
                'roles' => ['ROLE_USER'],
                'email' => 'forbiddenuser@example.com',
            ],
            'headers' => ['Content-Type' => 'application/ld+json']
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testForbiddenUpdateUser(): void
    {
        $client = $this->createAuthenticatedClient();
        $user = $this->getRepository(User::class)->findOneBy(['username' => 'test']);

        $client->request('PATCH', '/api/users/' . $user->getId(), [
            'json' => ['username' => 'illegalupdate'],
            'headers' => ['Content-Type' => 'application/merge-patch+json']
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testForbiddenDeleteUser(): void
    {
        $client = $this->createAuthenticatedClient();
        $user = $this->getRepository(User::class)->findOneBy(['username' => 'test']);

        $client->request('DELETE', '/api/users/' . $user->getId());

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
}
