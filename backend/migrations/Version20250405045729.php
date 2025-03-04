<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250405045729 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transaction_schedule ADD COLUMN repeat_frequency VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__transaction_schedule AS SELECT id, child_id, amount, created_at, updated_at, description, comment, next_execution_date, amount_base FROM transaction_schedule');
        $this->addSql('DROP TABLE transaction_schedule');
        $this->addSql('CREATE TABLE transaction_schedule (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, child_id INTEGER NOT NULL, amount DOUBLE PRECISION NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , updated_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , description VARCHAR(255) NOT NULL, comment VARCHAR(2000) DEFAULT NULL, next_execution_date DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , amount_base VARCHAR(255) NOT NULL, CONSTRAINT FK_144A3893DD62C21B FOREIGN KEY (child_id) REFERENCES child (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO transaction_schedule (id, child_id, amount, created_at, updated_at, description, comment, next_execution_date, amount_base) SELECT id, child_id, amount, created_at, updated_at, description, comment, next_execution_date, amount_base FROM __temp__transaction_schedule');
        $this->addSql('DROP TABLE __temp__transaction_schedule');
        $this->addSql('CREATE INDEX IDX_144A3893DD62C21B ON transaction_schedule (child_id)');
    }
}
