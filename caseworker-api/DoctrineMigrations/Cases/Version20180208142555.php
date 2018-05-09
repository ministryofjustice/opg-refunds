<?php

namespace DoctrineMigrations\Cases;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180208142555 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE report (id SERIAL NOT NULL, type VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, start_datetime TIMESTAMP(0) WITH TIME ZONE NOT NULL, end_datetime TIMESTAMP(0) WITH TIME ZONE NOT NULL, data JSON NOT NULL, generated_datetime TIMESTAMP(0) WITH TIME ZONE NOT NULL, generation_time_ms INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_report_type ON report (type)');
        $this->addSql('CREATE INDEX idx_report_type_title ON report (type, title)');
        $this->addSql('CREATE INDEX idx_report_type_start_datetime_end_datetime ON report (type, start_datetime, end_datetime)');
        $this->addSql('CREATE INDEX idx_note_created_datetime ON note (created_datetime)');
        $this->addSql('ALTER INDEX idx_type RENAME TO idx_note_type');
        $this->addSql('CREATE INDEX idx_claim_created_datetime ON claim (created_datetime)');
        $this->addSql('CREATE INDEX idx_claim_updated_datetime ON claim (updated_datetime)');
        $this->addSql('CREATE INDEX idx_claim_received_datetime ON claim (received_datetime)');
        $this->addSql('CREATE INDEX idx_claim_finished_datetime ON claim (finished_datetime)');
        $this->addSql('CREATE INDEX idx_claim_donor_name ON claim (donor_name)');
        $this->addSql('CREATE INDEX idx_claim_account_hash ON claim (account_hash)');
        $this->addSql('CREATE INDEX idx_claim_rejection_reason ON claim (rejection_reason)');
        $this->addSql('CREATE INDEX idx_claim_status_created_datetime ON claim (status, created_datetime)');
        $this->addSql('CREATE INDEX idx_claim_status_updated_datetime ON claim (status, updated_datetime)');
        $this->addSql('CREATE INDEX idx_claim_status_received_datetime ON claim (status, received_datetime)');
        $this->addSql('CREATE INDEX idx_claim_status_finished_datetime ON claim (status, finished_datetime)');
        $this->addSql('CREATE INDEX idx_claim_status_rejection_reason ON claim (status, rejection_reason)');
        $this->addSql('ALTER INDEX idx_status RENAME TO idx_claim_status');
        $this->addSql('CREATE INDEX idx_payment_amount ON payment (amount)');
        $this->addSql('CREATE INDEX idx_payment_method ON payment (method)');
        $this->addSql('CREATE INDEX idx_payment_added_datetime ON payment (added_datetime)');

        // Grant access to users
        $fullUsername = getenv('OPG_REFUNDS_DB_CASES_FULL_USERNAME');
        $this->addSql("GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO $fullUsername");
        $this->addSql("GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO $fullUsername");

        $migrationUsername = getenv('OPG_REFUNDS_DB_CASES_MIGRATION_USERNAME');
        $this->addSql("GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO $migrationUsername");
        $this->addSql("GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO $migrationUsername");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE report');
        $this->addSql('DROP INDEX idx_payment_amount');
        $this->addSql('DROP INDEX idx_payment_method');
        $this->addSql('DROP INDEX idx_payment_added_datetime');
        $this->addSql('DROP INDEX idx_claim_created_datetime');
        $this->addSql('DROP INDEX idx_claim_updated_datetime');
        $this->addSql('DROP INDEX idx_claim_received_datetime');
        $this->addSql('DROP INDEX idx_claim_finished_datetime');
        $this->addSql('DROP INDEX idx_claim_donor_name');
        $this->addSql('DROP INDEX idx_claim_account_hash');
        $this->addSql('DROP INDEX idx_claim_rejection_reason');
        $this->addSql('DROP INDEX idx_claim_status_created_datetime');
        $this->addSql('DROP INDEX idx_claim_status_updated_datetime');
        $this->addSql('DROP INDEX idx_claim_status_received_datetime');
        $this->addSql('DROP INDEX idx_claim_status_finished_datetime');
        $this->addSql('DROP INDEX idx_claim_status_rejection_reason');
        $this->addSql('ALTER INDEX idx_claim_status RENAME TO idx_status');
        $this->addSql('DROP INDEX idx_note_created_datetime');
        $this->addSql('ALTER INDEX idx_note_type RENAME TO idx_type');
    }
}
