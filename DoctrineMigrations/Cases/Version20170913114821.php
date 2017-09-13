<?php

namespace DoctrineMigrations\Cases;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170913114821 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE cases ALTER created_datetime TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE cases ALTER created_datetime DROP DEFAULT');
        $this->addSql('ALTER TABLE cases ALTER updated_datetime TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE cases ALTER updated_datetime DROP DEFAULT');
        $this->addSql('ALTER TABLE cases ALTER received_datetime TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE cases ALTER received_datetime DROP DEFAULT');
        $this->addSql('ALTER TABLE cases ALTER assigned_datetime TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE cases ALTER assigned_datetime DROP DEFAULT');
        $this->addSql('ALTER TABLE cases ALTER finished_datetime TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE cases ALTER finished_datetime DROP DEFAULT');
        $this->addSql('ALTER TABLE payment ALTER added_datetime TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE payment ALTER added_datetime DROP DEFAULT');
        $this->addSql('ALTER TABLE payment ALTER processed_datetime TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE payment ALTER processed_datetime DROP DEFAULT');
        $this->addSql('ALTER TABLE poa ALTER received_datetime TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE poa ALTER received_datetime DROP DEFAULT');
        $this->addSql('ALTER TABLE log ALTER created_datetime TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE log ALTER created_datetime DROP DEFAULT');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE log ALTER created_datetime TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE log ALTER created_datetime DROP DEFAULT');
        $this->addSql('ALTER TABLE payment ALTER added_datetime TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE payment ALTER added_datetime DROP DEFAULT');
        $this->addSql('ALTER TABLE payment ALTER processed_datetime TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE payment ALTER processed_datetime DROP DEFAULT');
        $this->addSql('ALTER TABLE cases ALTER created_datetime TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE cases ALTER created_datetime DROP DEFAULT');
        $this->addSql('ALTER TABLE cases ALTER updated_datetime TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE cases ALTER updated_datetime DROP DEFAULT');
        $this->addSql('ALTER TABLE cases ALTER received_datetime TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE cases ALTER received_datetime DROP DEFAULT');
        $this->addSql('ALTER TABLE cases ALTER assigned_datetime TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE cases ALTER assigned_datetime DROP DEFAULT');
        $this->addSql('ALTER TABLE cases ALTER finished_datetime TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE cases ALTER finished_datetime DROP DEFAULT');
        $this->addSql('ALTER TABLE poa ALTER received_datetime TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE poa ALTER received_datetime DROP DEFAULT');
    }
}
