<?php

namespace DoctrineMigrations\Cases;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171127160526 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE duplicate_claims (claim_id BIGINT NOT NULL, duplicate_claim_id BIGINT NOT NULL, PRIMARY KEY(claim_id, duplicate_claim_id))');
        $this->addSql('CREATE INDEX IDX_E766E0397096A49F ON duplicate_claims (claim_id)');
        $this->addSql('CREATE INDEX IDX_E766E03920E05070 ON duplicate_claims (duplicate_claim_id)');
        $this->addSql('ALTER TABLE duplicate_claims ADD CONSTRAINT FK_E766E0397096A49F FOREIGN KEY (claim_id) REFERENCES claim (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE duplicate_claims ADD CONSTRAINT FK_E766E03920E05070 FOREIGN KEY (duplicate_claim_id) REFERENCES claim (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

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
        $this->addSql('DROP TABLE duplicate_claims');
    }
}
