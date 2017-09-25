<?php

namespace DoctrineMigrations\Cases;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170926050627 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE verification DROP CONSTRAINT fk_5af1c50b7096a49f');
        $this->addSql('ALTER TABLE verification DROP CONSTRAINT FK_5AF1C50BBB18C0BA');
        $this->addSql('DROP INDEX uniq_5af1c50b7096a49f');
        $this->addSql('ALTER TABLE verification DROP claim_id');
        $this->addSql('ALTER TABLE verification ALTER poa_id TYPE INT');
        $this->addSql('ALTER TABLE verification ALTER poa_id DROP DEFAULT');
        $this->addSql('ALTER TABLE verification ADD CONSTRAINT FK_5AF1C50BBB18C0BA FOREIGN KEY (poa_id) REFERENCES poa (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE verification DROP CONSTRAINT fk_5af1c50bbb18c0ba');
        $this->addSql('ALTER TABLE verification ADD claim_id BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE verification ALTER poa_id TYPE BIGINT');
        $this->addSql('ALTER TABLE verification ALTER poa_id DROP DEFAULT');
        $this->addSql('ALTER TABLE verification ADD CONSTRAINT fk_5af1c50b7096a49f FOREIGN KEY (claim_id) REFERENCES claim (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE verification ADD CONSTRAINT fk_5af1c50bbb18c0ba FOREIGN KEY (poa_id) REFERENCES claim (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_5af1c50b7096a49f ON verification (claim_id)');
    }
}
