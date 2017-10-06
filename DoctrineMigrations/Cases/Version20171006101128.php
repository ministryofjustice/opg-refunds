<?php

namespace DoctrineMigrations\Cases;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171006101128 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE payment DROP CONSTRAINT fk_6d28840d7096a49f');
        $this->addSql('DROP INDEX uniq_6d28840d7096a49f');
        $this->addSql('ALTER TABLE payment DROP claim_id');
        $this->addSql('ALTER TABLE claim ADD payment_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE claim ADD CONSTRAINT FK_A769DE274C3A3BB FOREIGN KEY (payment_id) REFERENCES payment (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A769DE274C3A3BB ON claim (payment_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE payment ADD claim_id BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT fk_6d28840d7096a49f FOREIGN KEY (claim_id) REFERENCES claim (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_6d28840d7096a49f ON payment (claim_id)');
        $this->addSql('ALTER TABLE claim DROP CONSTRAINT FK_A769DE274C3A3BB');
        $this->addSql('DROP INDEX UNIQ_A769DE274C3A3BB');
        $this->addSql('ALTER TABLE claim DROP payment_id');
    }
}
