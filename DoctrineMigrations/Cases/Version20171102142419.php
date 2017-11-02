<?php

namespace DoctrineMigrations\Cases;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171102142419 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE claim ADD finished_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE claim ADD CONSTRAINT FK_A769DE274A12CC70 FOREIGN KEY (finished_by_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_A769DE274A12CC70 ON claim (finished_by_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE claim DROP CONSTRAINT FK_A769DE274A12CC70');
        $this->addSql('DROP INDEX IDX_A769DE274A12CC70');
        $this->addSql('ALTER TABLE claim DROP finished_by_id');
    }
}
