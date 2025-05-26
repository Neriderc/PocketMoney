<?php

namespace App\Tests\Api;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;

class LogoutApiTest extends BaseApiTestCase
{
    public function testGetUsersCollection(): void
    {
        $client = $this->createAuthenticatedClient($this->authToken);

        $client->request('POST', '/api/logout');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

    }

    public function testUnauthorizedAccess(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/logout');

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

}
