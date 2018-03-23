<?php declare(strict_types = 1);

namespace DoctrineMigrations\Cases;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180323161059 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE claim ALTER COLUMN json_data SET DATA TYPE JSONB USING json_data::jsonb');
        $this->addSql('COMMENT ON COLUMN claim.json_data IS \'(DC2Type:json_array)\'');
        $this->addSql('ALTER TABLE report ALTER COLUMN data SET DATA TYPE JSONB USING data::jsonb');
        $this->addSql('COMMENT ON COLUMN report.data IS \'(DC2Type:json_array)\'');

        $this->addSql('CREATE INDEX idx_claim_json_data_applicant ON claim((json_data->\'applicant\'))');
        $this->addSql('CREATE INDEX idx_claim_json_data_ad ON claim((json_data->\'ad\'))');
        $this->addSql('CREATE INDEX idx_claim_json_data_deceased ON claim((json_data->\'deceased\'))');
        $this->addSql('CREATE INDEX idx_claim_json_data_ad_meta_type ON claim((json_data->\'ad\'->\'meta\'->\'type\'))');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('COMMENT ON COLUMN claim.json_data IS NULL');
        $this->addSql('ALTER TABLE claim ALTER COLUMN json_data SET DATA TYPE JSON USING json_data::json');
        $this->addSql('COMMENT ON COLUMN report.data IS NULL');
        $this->addSql('ALTER TABLE report ALTER COLUMN data SET DATA TYPE JSON USING data::json');

        $this->addSql('DROP INDEX idx_claim_json_data_applicant');
        $this->addSql('DROP INDEX idx_claim_json_data_ad');
        $this->addSql('DROP INDEX idx_claim_json_data_deceased');
        $this->addSql('DROP INDEX idx_claim_json_data_ad_meta_type');
    }
}
