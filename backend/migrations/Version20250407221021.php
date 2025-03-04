<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250407221021 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE scheduled_transaction (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, child_id INTEGER NOT NULL, amount DOUBLE PRECISION NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , updated_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , description VARCHAR(255) NOT NULL, comment VARCHAR(2000) DEFAULT NULL, next_execution_date DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , amount_base VARCHAR(255) NOT NULL, repeat_frequency VARCHAR(255) DEFAULT NULL, CONSTRAINT FK_C0147C71DD62C21B FOREIGN KEY (child_id) REFERENCES child (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_C0147C71DD62C21B ON scheduled_transaction (child_id)');
        $this->addSql('CREATE TABLE scheduled_transaction_account (scheduled_transaction_id INTEGER NOT NULL, account_id INTEGER NOT NULL, PRIMARY KEY(scheduled_transaction_id, account_id), CONSTRAINT FK_331E34DEAA222510 FOREIGN KEY (scheduled_transaction_id) REFERENCES scheduled_transaction (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_331E34DE9B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_331E34DEAA222510 ON scheduled_transaction_account (scheduled_transaction_id)');
        $this->addSql('CREATE INDEX IDX_331E34DE9B6B5FBA ON scheduled_transaction_account (account_id)');
        $this->addSql('DROP TABLE transaction_schedule');
        $this->addSql('DROP TABLE transaction_schedule_account');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE transaction_schedule (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, child_id INTEGER NOT NULL, amount DOUBLE PRECISION NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , updated_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , description VARCHAR(255) NOT NULL COLLATE "BINARY", comment VARCHAR(2000) DEFAULT NULL COLLATE "BINARY", next_execution_date DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , amount_base VARCHAR(255) NOT NULL COLLATE "BINARY", repeat_frequency VARCHAR(255) DEFAULT NULL COLLATE "BINARY", CONSTRAINT FK_144A3893DD62C21B FOREIGN KEY (child_id) REFERENCES child (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_144A3893DD62C21B ON transaction_schedule (child_id)');
        $this->addSql('CREATE TABLE transaction_schedule_account (transaction_schedule_id INTEGER NOT NULL, account_id INTEGER NOT NULL, PRIMARY KEY(transaction_schedule_id, account_id), CONSTRAINT FK_85EF7B61882D7F8 FOREIGN KEY (transaction_schedule_id) REFERENCES transaction_schedule (id) ON UPDATE NO ACTION ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_85EF7B69B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id) ON UPDATE NO ACTION ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_85EF7B69B6B5FBA ON transaction_schedule_account (account_id)');
        $this->addSql('CREATE INDEX IDX_85EF7B61882D7F8 ON transaction_schedule_account (transaction_schedule_id)');
        $this->addSql('DROP TABLE scheduled_transaction');
        $this->addSql('DROP TABLE scheduled_transaction_account');
    }
}
