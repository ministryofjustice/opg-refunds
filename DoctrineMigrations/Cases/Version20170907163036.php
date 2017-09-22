<?php

namespace DoctrineMigrations\Cases;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170907163036 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE verification (id SERIAL NOT NULL, case_id INT DEFAULT NULL, poa_id INT DEFAULT NULL, type VARCHAR(255) NOT NULL, passes BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5AF1C50BCF10D4F5 ON verification (case_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5AF1C50BBB18C0BA ON verification (poa_id)');
        $this->addSql('CREATE TABLE log (id SERIAL NOT NULL, case_id INT DEFAULT NULL, caseworker_id INT DEFAULT NULL, poa_id INT DEFAULT NULL, created_datetime TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, message VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8F3F68C5CF10D4F5 ON log (case_id)');
        $this->addSql('CREATE INDEX IDX_8F3F68C5D29D852B ON log (caseworker_id)');
        $this->addSql('CREATE INDEX IDX_8F3F68C5BB18C0BA ON log (poa_id)');
        $this->addSql('CREATE TABLE cases (id SERIAL NOT NULL, assigned_to_id INT DEFAULT NULL, created_datetime TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_datetime TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, received_datetime TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, json_data VARCHAR(255) NOT NULL, status INT NOT NULL, assigned_datetime TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, finished_datetime TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, donor_name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_1C1B038BF4BD7827 ON cases (assigned_to_id)');
        $this->addSql('CREATE TABLE payment (id SERIAL NOT NULL, case_id INT DEFAULT NULL, amount NUMERIC(10, 0) NOT NULL, method VARCHAR(255) NOT NULL, added_datetime TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, processed_datetime TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6D28840DCF10D4F5 ON payment (case_id)');
        $this->addSql('CREATE TABLE poa (id SERIAL NOT NULL, case_id INT DEFAULT NULL, received_datetime TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, net_amount_paid NUMERIC(10, 0) NOT NULL, status INT NOT NULL, amount_to_refund NUMERIC(10, 0) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_736097E4CF10D4F5 ON poa (case_id)');
        $this->addSql('CREATE TABLE caseworker (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password_hash VARCHAR(255) NOT NULL, status INT NOT NULL, roles VARCHAR(255) NOT NULL, token VARCHAR(255) DEFAULT NULL, token_expires INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4763A912E7927C74 ON caseworker (email)');
        $this->addSql('ALTER TABLE verification ADD CONSTRAINT FK_5AF1C50BCF10D4F5 FOREIGN KEY (case_id) REFERENCES cases (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE verification ADD CONSTRAINT FK_5AF1C50BBB18C0BA FOREIGN KEY (poa_id) REFERENCES poa (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C5CF10D4F5 FOREIGN KEY (case_id) REFERENCES cases (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C5D29D852B FOREIGN KEY (caseworker_id) REFERENCES caseworker (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C5BB18C0BA FOREIGN KEY (poa_id) REFERENCES poa (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE cases ADD CONSTRAINT FK_1C1B038BF4BD7827 FOREIGN KEY (assigned_to_id) REFERENCES caseworker (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840DCF10D4F5 FOREIGN KEY (case_id) REFERENCES cases (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE poa ADD CONSTRAINT FK_736097E4CF10D4F5 FOREIGN KEY (case_id) REFERENCES cases (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        //Test Caseworkers
        $this->addSql('INSERT INTO caseworker (name, email, password_hash, status, roles) VALUES (\'Case Worker\', \'caseworker@test.com\', \'bd94dcda26fccb4e68d6a31f9b5aac0b571ae266d822620e901ef7ebe3a11d4f\', 1, \'CaseWorker\')');
        $this->addSql('INSERT INTO caseworker (name, email, password_hash, status, roles) VALUES (\'Reporting\', \'reporting@test.com\', \'bd94dcda26fccb4e68d6a31f9b5aac0b571ae266d822620e901ef7ebe3a11d4f\', 1, \'Reporting\')');
        $this->addSql('INSERT INTO caseworker (name, email, password_hash, status, roles) VALUES (\'Refund Manager\', \'refundmanager@test.com\', \'bd94dcda26fccb4e68d6a31f9b5aac0b571ae266d822620e901ef7ebe3a11d4f\', 1, \'RefundManager\')');
        $this->addSql('INSERT INTO caseworker (name, email, password_hash, status, roles) VALUES (\'Admin\', \'admin@test.com\', \'bd94dcda26fccb4e68d6a31f9b5aac0b571ae266d822620e901ef7ebe3a11d4f\', 1, \'Admin\')');
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
        $this->addSql('DROP TABLE verification');
        $this->addSql('DROP TABLE log');
        $this->addSql('DROP TABLE cases');
        $this->addSql('DROP TABLE payment');
        $this->addSql('DROP TABLE poa');
        $this->addSql('DROP TABLE caseworker');
    }
}
