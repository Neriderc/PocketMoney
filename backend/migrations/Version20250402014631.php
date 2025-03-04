<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250402014631 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE transaction_schedule_account (transaction_schedule_id INTEGER NOT NULL, account_id INTEGER NOT NULL, PRIMARY KEY(transaction_schedule_id, account_id), CONSTRAINT FK_85EF7B61882D7F8 FOREIGN KEY (transaction_schedule_id) REFERENCES transaction_schedule (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_85EF7B69B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_85EF7B61882D7F8 ON transaction_schedule_account (transaction_schedule_id)');
        $this->addSql('CREATE INDEX IDX_85EF7B69B6B5FBA ON transaction_schedule_account (account_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__transaction_schedule AS SELECT id, account_id, amount, created_at, updated_at, description, comment, next_execution_date, amount_base FROM transaction_schedule');
        $this->addSql('DROP TABLE transaction_schedule');
        $this->addSql('CREATE TABLE transaction_schedule (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, child_id INTEGER NOT NULL, amount DOUBLE PRECISION NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , updated_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , description VARCHAR(255) NOT NULL, comment VARCHAR(2000) DEFAULT NULL, next_execution_date DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , amount_base VARCHAR(255) NOT NULL, CONSTRAINT FK_144A3893DD62C21B FOREIGN KEY (child_id) REFERENCES child (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO transaction_schedule (id, child_id, amount, created_at, updated_at, description, comment, next_execution_date, amount_base) SELECT id, account_id, amount, created_at, updated_at, description, comment, next_execution_date, amount_base FROM __temp__transaction_schedule');
        $this->addSql('DROP TABLE __temp__transaction_schedule');
        $this->addSql('CREATE INDEX IDX_144A3893DD62C21B ON transaction_schedule (child_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE transaction_schedule_account');
        $this->addSql('CREATE TEMPORARY TABLE __temp__transaction_schedule AS SELECT id, child_id, amount, created_at, updated_at, description, comment, next_execution_date, amount_base FROM transaction_schedule');
        $this->addSql('DROP TABLE transaction_schedule');
        $this->addSql('CREATE TABLE transaction_schedule (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, account_id INTEGER NOT NULL, amount DOUBLE PRECISION NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , updated_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , description VARCHAR(255) NOT NULL, comment VARCHAR(2000) DEFAULT NULL, next_execution_date DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , amount_base VARCHAR(255) NOT NULL, CONSTRAINT FK_144A38939B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO transaction_schedule (id, account_id, amount, created_at, updated_at, description, comment, next_execution_date, amount_base) SELECT id, child_id, amount, created_at, updated_at, description, comment, next_execution_date, amount_base FROM __temp__transaction_schedule');
        $this->addSql('DROP TABLE __temp__transaction_schedule');
        $this->addSql('CREATE INDEX IDX_144A38939B6B5FBA ON transaction_schedule (account_id)');
    }
}
