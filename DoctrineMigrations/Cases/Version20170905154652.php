<?php

namespace DoctrineMigrations\Cases;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170905154652 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE verification_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE log_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE cases_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE payment_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE poa_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE verification (id INT NOT NULL, refund_case_id INT NOT NULL, poa_id INT NOT NULL, type VARCHAR(255) NOT NULL, passes BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE log (id INT NOT NULL, refund_case_id INT NOT NULL, caseworker_id INT NOT NULL, poa_id INT NOT NULL, created_datetime TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, message VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE cases (id INT NOT NULL, created_datetime TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_datetime TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, received_datetime TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, json_data VARCHAR(255) NOT NULL, status INT NOT NULL, assigned_to_id INT NOT NULL, assigned_datetime TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, finished_datetime TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, donor_name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE payment (id INT NOT NULL, refund_case_id INT NOT NULL, amount NUMERIC(10, 0) NOT NULL, method VARCHAR(255) NOT NULL, added_datetime TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, processed_datetime TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE poa (id INT NOT NULL, refund_case_id INT NOT NULL, received_datetime TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, net_amount_paid NUMERIC(10, 0) NOT NULL, status INT NOT NULL, amount_to_refund NUMERIC(10, 0) NOT NULL, PRIMARY KEY(id))');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE verification_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE log_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE cases_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE payment_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE poa_id_seq CASCADE');
        $this->addSql('DROP TABLE verification');
        $this->addSql('DROP TABLE log');
        $this->addSql('DROP TABLE cases');
        $this->addSql('DROP TABLE payment');
        $this->addSql('DROP TABLE poa');
    }
}
