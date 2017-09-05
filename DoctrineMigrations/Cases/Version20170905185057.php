<?php

namespace DoctrineMigrations\Cases;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170905185057 extends AbstractMigration
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
        $this->addSql('CREATE SEQUENCE caseworker_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE verification (id INT NOT NULL, case_id INT DEFAULT NULL, poa_id INT DEFAULT NULL, type VARCHAR(255) NOT NULL, passes BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5AF1C50BCF10D4F5 ON verification (case_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5AF1C50BBB18C0BA ON verification (poa_id)');
        $this->addSql('CREATE TABLE log (id INT NOT NULL, case_id INT DEFAULT NULL, caseworker_id INT DEFAULT NULL, poa_id INT DEFAULT NULL, created_datetime TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, message VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8F3F68C5CF10D4F5 ON log (case_id)');
        $this->addSql('CREATE INDEX IDX_8F3F68C5D29D852B ON log (caseworker_id)');
        $this->addSql('CREATE INDEX IDX_8F3F68C5BB18C0BA ON log (poa_id)');
        $this->addSql('CREATE TABLE cases (id INT NOT NULL, assigned_to_id INT DEFAULT NULL, created_datetime TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_datetime TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, received_datetime TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, json_data VARCHAR(255) NOT NULL, status INT NOT NULL, assigned_datetime TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, finished_datetime TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, donor_name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_1C1B038BF4BD7827 ON cases (assigned_to_id)');
        $this->addSql('CREATE TABLE payment (id INT NOT NULL, case_id INT DEFAULT NULL, amount NUMERIC(10, 0) NOT NULL, method VARCHAR(255) NOT NULL, added_datetime TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, processed_datetime TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6D28840DCF10D4F5 ON payment (case_id)');
        $this->addSql('CREATE TABLE poa (id INT NOT NULL, case_id INT DEFAULT NULL, received_datetime TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, net_amount_paid NUMERIC(10, 0) NOT NULL, status INT NOT NULL, amount_to_refund NUMERIC(10, 0) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_736097E4CF10D4F5 ON poa (case_id)');
        $this->addSql('CREATE TABLE caseworker (id INT NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password_hash VARCHAR(255) NOT NULL, status INT NOT NULL, roles VARCHAR(255) NOT NULL, token VARCHAR(255) NOT NULL, token_expires INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE verification ADD CONSTRAINT FK_5AF1C50BCF10D4F5 FOREIGN KEY (case_id) REFERENCES cases (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE verification ADD CONSTRAINT FK_5AF1C50BBB18C0BA FOREIGN KEY (poa_id) REFERENCES poa (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C5CF10D4F5 FOREIGN KEY (case_id) REFERENCES cases (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C5D29D852B FOREIGN KEY (caseworker_id) REFERENCES caseworker (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C5BB18C0BA FOREIGN KEY (poa_id) REFERENCES poa (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE cases ADD CONSTRAINT FK_1C1B038BF4BD7827 FOREIGN KEY (assigned_to_id) REFERENCES caseworker (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840DCF10D4F5 FOREIGN KEY (case_id) REFERENCES cases (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE poa ADD CONSTRAINT FK_736097E4CF10D4F5 FOREIGN KEY (case_id) REFERENCES cases (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE verification DROP CONSTRAINT FK_5AF1C50BCF10D4F5');
        $this->addSql('ALTER TABLE log DROP CONSTRAINT FK_8F3F68C5CF10D4F5');
        $this->addSql('ALTER TABLE payment DROP CONSTRAINT FK_6D28840DCF10D4F5');
        $this->addSql('ALTER TABLE poa DROP CONSTRAINT FK_736097E4CF10D4F5');
        $this->addSql('ALTER TABLE verification DROP CONSTRAINT FK_5AF1C50BBB18C0BA');
        $this->addSql('ALTER TABLE log DROP CONSTRAINT FK_8F3F68C5BB18C0BA');
        $this->addSql('ALTER TABLE log DROP CONSTRAINT FK_8F3F68C5D29D852B');
        $this->addSql('ALTER TABLE cases DROP CONSTRAINT FK_1C1B038BF4BD7827');
        $this->addSql('DROP SEQUENCE verification_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE log_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE cases_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE payment_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE poa_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE caseworker_id_seq CASCADE');
        $this->addSql('DROP TABLE verification');
        $this->addSql('DROP TABLE log');
        $this->addSql('DROP TABLE cases');
        $this->addSql('DROP TABLE payment');
        $this->addSql('DROP TABLE poa');
        $this->addSql('DROP TABLE caseworker');
    }
}
