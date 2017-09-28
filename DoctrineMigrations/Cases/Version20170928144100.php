<?php

namespace DoctrineMigrations\Cases;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170928144100 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE payment (id SERIAL NOT NULL, claim_id BIGINT DEFAULT NULL, amount NUMERIC(10, 0) NOT NULL, method VARCHAR(255) NOT NULL, added_datetime TIMESTAMP(0) WITH TIME ZONE NOT NULL, processed_datetime TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6D28840D7096A49F ON payment (claim_id)');
        $this->addSql('CREATE TABLE verification (id SERIAL NOT NULL, poa_id INT DEFAULT NULL, type VARCHAR(255) NOT NULL, passes BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_5AF1C50BBB18C0BA ON verification (poa_id)');
        $this->addSql('CREATE TABLE "user" (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password_hash VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, roles VARCHAR(255) NOT NULL, token VARCHAR(255) DEFAULT NULL, token_expires INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)');
        $this->addSql('CREATE TABLE claim (id BIGINT NOT NULL, assigned_to_id INT DEFAULT NULL, created_datetime TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_datetime TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, received_datetime TIMESTAMP(0) WITH TIME ZONE NOT NULL, json_data BYTEA NOT NULL, status VARCHAR(255) NOT NULL, assigned_datetime TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, finished_datetime TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, donor_name VARCHAR(255) NOT NULL, account_hash VARCHAR(255) NOT NULL, no_sirius_poas BOOLEAN NOT NULL, no_meris_poas BOOLEAN NOT NULL, rejection_reason VARCHAR(255) DEFAULT NULL, rejection_reason_description VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A769DE27F4BD7827 ON claim (assigned_to_id)');
        $this->addSql('CREATE TABLE poa (id SERIAL NOT NULL, claim_id BIGINT DEFAULT NULL, system VARCHAR(255) NOT NULL, case_number VARCHAR(255) NOT NULL, received_date DATE NOT NULL, original_payment_amount VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_736097E47096A49F ON poa (claim_id)');
        $this->addSql('CREATE TABLE log (id SERIAL NOT NULL, claim_id BIGINT DEFAULT NULL, user_id INT DEFAULT NULL, poa_id INT DEFAULT NULL, created_datetime TIMESTAMP(0) WITH TIME ZONE NOT NULL, title VARCHAR(255) NOT NULL, message TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8F3F68C57096A49F ON log (claim_id)');
        $this->addSql('CREATE INDEX IDX_8F3F68C5A76ED395 ON log (user_id)');
        $this->addSql('CREATE INDEX IDX_8F3F68C5BB18C0BA ON log (poa_id)');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D7096A49F FOREIGN KEY (claim_id) REFERENCES claim (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE verification ADD CONSTRAINT FK_5AF1C50BBB18C0BA FOREIGN KEY (poa_id) REFERENCES poa (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE claim ADD CONSTRAINT FK_A769DE27F4BD7827 FOREIGN KEY (assigned_to_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE poa ADD CONSTRAINT FK_736097E47096A49F FOREIGN KEY (claim_id) REFERENCES claim (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C57096A49F FOREIGN KEY (claim_id) REFERENCES claim (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C5A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C5BB18C0BA FOREIGN KEY (poa_id) REFERENCES poa (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        // Grant access to users
        $fullUsername = getenv('OPG_REFUNDS_DB_CASES_FULL_USERNAME');
        $this->addSql("GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO $fullUsername");
        $this->addSql("GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO $fullUsername");

        $migrationUsername = getenv('OPG_REFUNDS_DB_CASES_MIGRATION_USERNAME');
        $this->addSql("GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO $migrationUsername");
        $this->addSql("GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO $migrationUsername");

        // Test Caseworkers
        $this->addSql('INSERT INTO "user" (name, email, password_hash, status, roles) VALUES (\'Case Worker 01\', \'caseworker01@refunds.uat\', \'bd94dcda26fccb4e68d6a31f9b5aac0b571ae266d822620e901ef7ebe3a11d4f\', \'active\', \'Caseworker\')');
        $this->addSql('INSERT INTO "user" (name, email, password_hash, status, roles) VALUES (\'Case Worker 02\', \'caseworker02@refunds.uat\', \'bd94dcda26fccb4e68d6a31f9b5aac0b571ae266d822620e901ef7ebe3a11d4f\', \'active\', \'Caseworker\')');
        $this->addSql('INSERT INTO "user" (name, email, password_hash, status, roles) VALUES (\'Case Worker 03\', \'caseworker03@refunds.uat\', \'bd94dcda26fccb4e68d6a31f9b5aac0b571ae266d822620e901ef7ebe3a11d4f\', \'active\', \'Caseworker\')');
        $this->addSql('INSERT INTO "user" (name, email, password_hash, status, roles) VALUES (\'Case Worker 04\', \'caseworker04@refunds.uat\', \'bd94dcda26fccb4e68d6a31f9b5aac0b571ae266d822620e901ef7ebe3a11d4f\', \'active\', \'Caseworker\')');
        $this->addSql('INSERT INTO "user" (name, email, password_hash, status, roles) VALUES (\'Case Worker 05\', \'caseworker05@refunds.uat\', \'bd94dcda26fccb4e68d6a31f9b5aac0b571ae266d822620e901ef7ebe3a11d4f\', \'active\', \'Caseworker\')');
        $this->addSql('INSERT INTO "user" (name, email, password_hash, status, roles) VALUES (\'Case Worker 06\', \'caseworker06@refunds.uat\', \'bd94dcda26fccb4e68d6a31f9b5aac0b571ae266d822620e901ef7ebe3a11d4f\', \'active\', \'Caseworker\')');
        $this->addSql('INSERT INTO "user" (name, email, password_hash, status, roles) VALUES (\'Case Worker 07\', \'caseworker07@refunds.uat\', \'bd94dcda26fccb4e68d6a31f9b5aac0b571ae266d822620e901ef7ebe3a11d4f\', \'active\', \'Caseworker\')');
        $this->addSql('INSERT INTO "user" (name, email, password_hash, status, roles) VALUES (\'Case Worker 08\', \'caseworker08@refunds.uat\', \'bd94dcda26fccb4e68d6a31f9b5aac0b571ae266d822620e901ef7ebe3a11d4f\', \'active\', \'Caseworker\')');
        $this->addSql('INSERT INTO "user" (name, email, password_hash, status, roles) VALUES (\'Case Worker 09\', \'caseworker09@refunds.uat\', \'bd94dcda26fccb4e68d6a31f9b5aac0b571ae266d822620e901ef7ebe3a11d4f\', \'active\', \'Caseworker\')');
        $this->addSql('INSERT INTO "user" (name, email, password_hash, status, roles) VALUES (\'Case Worker 10\', \'caseworker10@refunds.uat\', \'bd94dcda26fccb4e68d6a31f9b5aac0b571ae266d822620e901ef7ebe3a11d4f\', \'active\', \'Caseworker\')');
        $this->addSql('INSERT INTO "user" (name, email, password_hash, status, roles) VALUES (\'Refund Manager 01\', \'refundmanager01@refunds.uat\', \'bd94dcda26fccb4e68d6a31f9b5aac0b571ae266d822620e901ef7ebe3a11d4f\', \'active\', \'RefundManager,Caseworker\')');
        $this->addSql('INSERT INTO "user" (name, email, password_hash, status, roles) VALUES (\'Refund Manager 02\', \'refundmanager02@refunds.uat\', \'bd94dcda26fccb4e68d6a31f9b5aac0b571ae266d822620e901ef7ebe3a11d4f\', \'active\', \'RefundManager,Caseworker\')');
        $this->addSql('INSERT INTO "user" (name, email, password_hash, status, roles) VALUES (\'Refund Manager 03\', \'refundmanager03@refunds.uat\', \'bd94dcda26fccb4e68d6a31f9b5aac0b571ae266d822620e901ef7ebe3a11d4f\', \'active\', \'RefundManager,Caseworker\')');
        $this->addSql('INSERT INTO "user" (name, email, password_hash, status, roles) VALUES (\'Refund Manager 04\', \'refundmanager04@refunds.uat\', \'bd94dcda26fccb4e68d6a31f9b5aac0b571ae266d822620e901ef7ebe3a11d4f\', \'active\', \'RefundManager,Caseworker\')');
        $this->addSql('INSERT INTO "user" (name, email, password_hash, status, roles) VALUES (\'Refund Manager 05\', \'refundmanager05@refunds.uat\', \'bd94dcda26fccb4e68d6a31f9b5aac0b571ae266d822620e901ef7ebe3a11d4f\', \'active\', \'RefundManager,Caseworker\')');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE claim DROP CONSTRAINT FK_A769DE27F4BD7827');
        $this->addSql('ALTER TABLE log DROP CONSTRAINT FK_8F3F68C5A76ED395');
        $this->addSql('ALTER TABLE payment DROP CONSTRAINT FK_6D28840D7096A49F');
        $this->addSql('ALTER TABLE poa DROP CONSTRAINT FK_736097E47096A49F');
        $this->addSql('ALTER TABLE log DROP CONSTRAINT FK_8F3F68C57096A49F');
        $this->addSql('ALTER TABLE verification DROP CONSTRAINT FK_5AF1C50BBB18C0BA');
        $this->addSql('ALTER TABLE log DROP CONSTRAINT FK_8F3F68C5BB18C0BA');
        $this->addSql('DROP TABLE payment');
        $this->addSql('DROP TABLE verification');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('DROP TABLE claim');
        $this->addSql('DROP TABLE poa');
        $this->addSql('DROP TABLE log');
    }
}
