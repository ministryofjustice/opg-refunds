<?php

namespace DoctrineMigrations\Cases;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170920165530 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE cases DROP CONSTRAINT fk_1c1b038bf4bd7827');
        $this->addSql('ALTER TABLE log DROP CONSTRAINT fk_8f3f68c5d29d852b');
        $this->addSql('ALTER TABLE verification DROP CONSTRAINT fk_5af1c50bcf10d4f5');
        $this->addSql('ALTER TABLE payment DROP CONSTRAINT fk_6d28840dcf10d4f5');
        $this->addSql('ALTER TABLE poa DROP CONSTRAINT fk_736097e4cf10d4f5');
        $this->addSql('ALTER TABLE log DROP CONSTRAINT fk_8f3f68c5cf10d4f5');
        $this->addSql('DROP SEQUENCE caseworker_id_seq CASCADE');
        $this->addSql('CREATE TABLE "user" (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password_hash VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, roles VARCHAR(255) NOT NULL, token VARCHAR(255) DEFAULT NULL, token_expires INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)');
        $this->addSql('CREATE TABLE claim (id BIGINT NOT NULL, assigned_to_id INT DEFAULT NULL, created_datetime TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_datetime TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, received_datetime TIMESTAMP(0) WITH TIME ZONE NOT NULL, json_data BYTEA NOT NULL, status VARCHAR(255) NOT NULL, assigned_datetime TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, finished_datetime TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, donor_name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A769DE27F4BD7827 ON claim (assigned_to_id)');
        $this->addSql('ALTER TABLE claim ADD CONSTRAINT FK_A769DE27F4BD7827 FOREIGN KEY (assigned_to_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('DROP TABLE caseworker');
        $this->addSql('DROP TABLE cases');
        $this->addSql('DROP INDEX uniq_5af1c50bcf10d4f5');
        $this->addSql('ALTER TABLE verification RENAME COLUMN case_id TO claim_id');
        $this->addSql('ALTER TABLE verification ADD CONSTRAINT FK_5AF1C50B7096A49F FOREIGN KEY (claim_id) REFERENCES claim (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5AF1C50B7096A49F ON verification (claim_id)');
        $this->addSql('DROP INDEX idx_8f3f68c5cf10d4f5');
        $this->addSql('DROP INDEX idx_8f3f68c5d29d852b');
        $this->addSql('ALTER TABLE log RENAME COLUMN case_id TO claim_id');
        $this->addSql('ALTER TABLE log RENAME COLUMN caseworker_id TO user_id');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C57096A49F FOREIGN KEY (claim_id) REFERENCES claim (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C5A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_8F3F68C57096A49F ON log (claim_id)');
        $this->addSql('CREATE INDEX IDX_8F3F68C5A76ED395 ON log (user_id)');
        $this->addSql('DROP INDEX uniq_6d28840dcf10d4f5');
        $this->addSql('ALTER TABLE payment RENAME COLUMN case_id TO claim_id');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D7096A49F FOREIGN KEY (claim_id) REFERENCES claim (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6D28840D7096A49F ON payment (claim_id)');
        $this->addSql('DROP INDEX idx_736097e4cf10d4f5');
        $this->addSql('ALTER TABLE poa RENAME COLUMN case_id TO claim_id');
        $this->addSql('ALTER TABLE poa ADD CONSTRAINT FK_736097E47096A49F FOREIGN KEY (claim_id) REFERENCES claim (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_736097E47096A49F ON poa (claim_id)');

        $fullUsername = getenv('OPG_REFUNDS_DB_CASES_FULL_USERNAME');
        $this->addSql("GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO $fullUsername");
        $this->addSql("GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO $fullUsername");

        $migrationUsername = getenv('OPG_REFUNDS_DB_CASES_MIGRATION_USERNAME');
        $this->addSql("GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO $migrationUsername");
        $this->addSql("GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO $migrationUsername");

        //Test Caseworkers
        $this->addSql('INSERT INTO "user" (name, email, password_hash, status, roles) VALUES (\'Case Worker\', \'caseworker@test.com\', \'bd94dcda26fccb4e68d6a31f9b5aac0b571ae266d822620e901ef7ebe3a11d4f\', \'active\', \'Caseworker\')');
        $this->addSql('INSERT INTO "user" (name, email, password_hash, status, roles) VALUES (\'Reporting\', \'reporting@test.com\', \'bd94dcda26fccb4e68d6a31f9b5aac0b571ae266d822620e901ef7ebe3a11d4f\', \'active\', \'Reporting\')');
        $this->addSql('INSERT INTO "user" (name, email, password_hash, status, roles) VALUES (\'Refund Manager\', \'refundmanager@test.com\', \'bd94dcda26fccb4e68d6a31f9b5aac0b571ae266d822620e901ef7ebe3a11d4f\', \'active\', \'RefundManager\')');
        $this->addSql('INSERT INTO "user" (name, email, password_hash, status, roles) VALUES (\'Admin\', \'admin@test.com\', \'bd94dcda26fccb4e68d6a31f9b5aac0b571ae266d822620e901ef7ebe3a11d4f\', \'active\', \'Admin\')');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE log DROP CONSTRAINT FK_8F3F68C5A76ED395');
        $this->addSql('ALTER TABLE claim DROP CONSTRAINT FK_A769DE27F4BD7827');
        $this->addSql('ALTER TABLE verification DROP CONSTRAINT FK_5AF1C50B7096A49F');
        $this->addSql('ALTER TABLE log DROP CONSTRAINT FK_8F3F68C57096A49F');
        $this->addSql('ALTER TABLE payment DROP CONSTRAINT FK_6D28840D7096A49F');
        $this->addSql('ALTER TABLE poa DROP CONSTRAINT FK_736097E47096A49F');
        $this->addSql('CREATE SEQUENCE caseworker_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE caseworker (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password_hash VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, roles VARCHAR(255) NOT NULL, token VARCHAR(255) DEFAULT NULL, token_expires INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_4763a912e7927c74 ON caseworker (email)');
        $this->addSql('CREATE TABLE cases (id BIGINT NOT NULL, assigned_to_id INT DEFAULT NULL, created_datetime TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_datetime TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, received_datetime TIMESTAMP(0) WITH TIME ZONE NOT NULL, json_data BYTEA NOT NULL, status VARCHAR(255) NOT NULL, assigned_datetime TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, finished_datetime TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, donor_name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_1c1b038bf4bd7827 ON cases (assigned_to_id)');
        $this->addSql('ALTER TABLE cases ADD CONSTRAINT fk_1c1b038bf4bd7827 FOREIGN KEY (assigned_to_id) REFERENCES caseworker (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('DROP TABLE claim');
        $this->addSql('DROP INDEX UNIQ_5AF1C50B7096A49F');
        $this->addSql('ALTER TABLE verification RENAME COLUMN claim_id TO case_id');
        $this->addSql('ALTER TABLE verification ADD CONSTRAINT fk_5af1c50bcf10d4f5 FOREIGN KEY (case_id) REFERENCES cases (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_5af1c50bcf10d4f5 ON verification (case_id)');
        $this->addSql('DROP INDEX UNIQ_6D28840D7096A49F');
        $this->addSql('ALTER TABLE payment RENAME COLUMN claim_id TO case_id');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT fk_6d28840dcf10d4f5 FOREIGN KEY (case_id) REFERENCES cases (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_6d28840dcf10d4f5 ON payment (case_id)');
        $this->addSql('DROP INDEX IDX_736097E47096A49F');
        $this->addSql('ALTER TABLE poa RENAME COLUMN claim_id TO case_id');
        $this->addSql('ALTER TABLE poa ADD CONSTRAINT fk_736097e4cf10d4f5 FOREIGN KEY (case_id) REFERENCES cases (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_736097e4cf10d4f5 ON poa (case_id)');
        $this->addSql('DROP INDEX IDX_8F3F68C57096A49F');
        $this->addSql('DROP INDEX IDX_8F3F68C5A76ED395');
        $this->addSql('ALTER TABLE log RENAME COLUMN user_id TO caseworker_id');
        $this->addSql('ALTER TABLE log RENAME COLUMN claim_id TO case_id');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT fk_8f3f68c5d29d852b FOREIGN KEY (caseworker_id) REFERENCES caseworker (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT fk_8f3f68c5cf10d4f5 FOREIGN KEY (case_id) REFERENCES cases (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_8f3f68c5cf10d4f5 ON log (case_id)');
        $this->addSql('CREATE INDEX idx_8f3f68c5d29d852b ON log (caseworker_id)');
    }
}
