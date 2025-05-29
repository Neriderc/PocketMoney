<?php

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\DataFixtures\BaseFixture;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;

abstract class BaseApiTestCase extends ApiTestCase
{
    protected EntityManagerInterface $entityManager;
    protected string $authToken;
    protected string $adminAuthToken;
    protected $referenceRepository;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $container = static::getContainer();
        $this->entityManager = $container->get('doctrine')->getManager();
        $this->entityManager->beginTransaction();
        $this->loadFixtures();
        $this->authenticateUsers();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if ($this->entityManager->getConnection()->isTransactionActive()) {
            $this->entityManager->rollback();
        }

        $this->entityManager->close();
    }

    protected function loadFixtures(): void
    {
        $fixture = static::getContainer()->get(BaseFixture::class);

        $loader = new Loader();
        $loader->addFixture($fixture);

        $purger = new ORMPurger($this->entityManager);
        $executor = new ORMExecutor($this->entityManager, $purger);
        $executor->execute($loader->getFixtures());
        $this->referenceRepository = $executor->getReferenceRepository();
    }

    protected function authenticateUsers(): void
    {
        $response = static::createClient()->request('POST', 'api/login_check', [
            'json' => [
                'username' => 'test',
                'password' => 'test',
            ],
        ]);
        $this->authToken = $response->toArray()['token'];

        $adminResponse = static::createClient()->request('POST', 'api/login_check', [
            'json' => [
                'username' => 'admin',
                'password' => 'admin',
            ],
        ]);
        $this->adminAuthToken = $adminResponse->toArray()['token'];
    }

    protected function createAuthenticatedClient(string $token = null): Client
    {
        return static::createClient([], [
            'headers' => [
                'Authorization' => 'Bearer ' . ($token ?? $this->authToken),
                'Content-Type' => 'application/json',
            ]
        ]);
    }

    protected function getRepository(string $entityClass)
    {
        return $this->entityManager->getRepository($entityClass);
    }

    protected function assertEntityExists(string $entityClass, array $criteria): void
    {
        $entity = $this->getRepository($entityClass)->findOneBy($criteria);
        $this->assertNotNull($entity, sprintf(
            "Failed to find %s with criteria: %s",
            $entityClass,
            json_encode($criteria)
        ));
    }

    protected function assertEntityNotExists(string $entityClass, array $criteria): void
    {
        $entity = $this->getRepository($entityClass)->findOneBy($criteria);
        $this->assertNull($entity, sprintf(
                "Unexpectedly found %s with criteria: %s",
                $entityClass,
                json_encode($criteria))
        );
    }

    protected function getFixtureReference(string $name, string $class)
    {
        return $this->referenceRepository->getReference($name, $class);
    }
}