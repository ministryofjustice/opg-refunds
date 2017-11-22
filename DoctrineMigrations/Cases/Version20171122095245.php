<?php

namespace DoctrineMigrations\Cases;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171122095245 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE note RENAME COLUMN title TO type');

        $this->addSql('UPDATE note SET type = \'user_note\' WHERE type = \'Caseworker note\'');
        $this->addSql('UPDATE note SET type = \'claim_submitted\' WHERE type = \'Claim submitted\'');
        $this->addSql('UPDATE note SET type = \'assisted_digital\' WHERE type = \'Assisted Digital\'');
        $this->addSql('UPDATE note SET type = \'claim_pending\' WHERE type = \'Claim returned\'');
        $this->addSql('UPDATE note SET type = \'claim_in_progress\' WHERE type = \'Claim started by caseworker\'');
        $this->addSql('UPDATE note SET type = \'claim_duplicate\' WHERE type = \'Duplicate claim\'');
        $this->addSql('UPDATE note SET type = \'claim_rejected\' WHERE type = \'Claim rejected\'');
        $this->addSql('UPDATE note SET type = \'claim_accepted\' WHERE type = \'Claim accepted\'');
        $this->addSql('UPDATE note SET type = \'poa_added\' WHERE type = \'POA added\'');
        $this->addSql('UPDATE note SET type = \'poa_edited\' WHERE type = \'POA edited\'');
        $this->addSql('UPDATE note SET type = \'poa_deleted\' WHERE type = \'POA delete\'');
        $this->addSql('UPDATE note SET type = \'no_meris_poas\' WHERE type = \'No Meris POAs\'');
        $this->addSql('UPDATE note SET type = \'meris_poas_found\' WHERE type = \'Meris POA found\'');
        $this->addSql('UPDATE note SET type = \'no_sirius_poas\' WHERE type = \'No Sirius POAs\'');
        $this->addSql('UPDATE note SET type = \'sirius_poas_found\' WHERE type = \'Sirius POA found\'');
        $this->addSql('UPDATE note SET type = \'refund_added\' WHERE type = \'Refund added\'');
        $this->addSql('UPDATE note SET type = \'refund_updated\' WHERE type = \'Refund updated\'');
        $this->addSql('UPDATE note SET type = \'refund_downloaded\' WHERE type = \'Refund downloaded\'');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE note RENAME COLUMN type TO title');
    }
}
