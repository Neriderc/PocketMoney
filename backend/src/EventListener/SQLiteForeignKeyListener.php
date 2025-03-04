<?php
namespace App\EventListener;

use Doctrine\ORM\EntityManagerInterface;

readonly class SQLiteForeignKeyListener
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function postConnect(): void
    {
        $connection = $this->entityManager->getConnection();

        // Enable foreign keys to allow set null on delete
        if ($connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\SqlitePlatform) {
            $connection->executeStatement('PRAGMA foreign_keys = ON;');
        }
    }
}