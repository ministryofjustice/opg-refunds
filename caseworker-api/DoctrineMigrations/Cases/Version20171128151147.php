<?php

namespace DoctrineMigrations\Cases;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171128151147 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP INDEX case_number_idx');
        $this->addSql('ALTER TABLE poa ADD case_number_available BOOLEAN DEFAULT \'false\' NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX case_number_idx ON poa (system, case_number, case_number_available)');

        $this->addSql('UPDATE poa SET case_number_available = true WHERE claim_id in (SELECT id FROM claim WHERE status = \'rejected\')');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP INDEX case_number_idx');
        $this->addSql('ALTER TABLE poa DROP case_number_available');
        $this->addSql('CREATE UNIQUE INDEX case_number_idx ON poa (system, case_number)');
    }
}
