<?php

namespace DoctrineMigrations\Cases;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170908081836 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE cases ALTER updated_datetime DROP NOT NULL');
        $this->addSql('ALTER TABLE cases ALTER assigned_datetime DROP NOT NULL');
        $this->addSql('ALTER TABLE cases ALTER finished_datetime DROP NOT NULL');

        $this->addSql('INSERT INTO cases (assigned_to_id, created_datetime, received_datetime, json_data, status, donor_name) VALUES (1, NOW(), NOW(), \'{}\', 1, \'Test Donor 1\')');
        $this->addSql('INSERT INTO cases (assigned_to_id, created_datetime, received_datetime, json_data, status, donor_name) VALUES (1, NOW(), NOW(), \'{}\', 1, \'Test Donor 2\')');
        $this->addSql('INSERT INTO cases (assigned_to_id, created_datetime, received_datetime, json_data, status, donor_name) VALUES (1, NOW(), NOW(), \'{}\', 1, \'Test Donor 3\')');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE cases ALTER updated_datetime SET NOT NULL');
        $this->addSql('ALTER TABLE cases ALTER assigned_datetime SET NOT NULL');
        $this->addSql('ALTER TABLE cases ALTER finished_datetime SET NOT NULL');
    }
}
