<?php

declare(strict_types = 1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180324210945 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE blocked_money_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE balance_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE transaction_log_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE blocked_money (id INT NOT NULL, user_id INT NOT NULL, sum BIGINT NOT NULL, uuid VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9E5218D17F50A6 ON blocked_money (uuid)');
        $this->addSql('CREATE TABLE balance (id INT NOT NULL, user_id INT NOT NULL, sum BIGINT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE transaction_log (id INT NOT NULL, type INT NOT NULL, sum BIGINT NOT NULL, user_id INT NOT NULL, details JSON NOT NULL, PRIMARY KEY(id))');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE blocked_money_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE balance_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE transaction_log_id_seq CASCADE');
        $this->addSql('DROP TABLE blocked_money');
        $this->addSql('DROP TABLE balance');
        $this->addSql('DROP TABLE transaction_log');
    }
}
