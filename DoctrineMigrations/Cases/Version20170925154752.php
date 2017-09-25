<?php

namespace DoctrineMigrations\Cases;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170925154752 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE verification DROP CONSTRAINT FK_5AF1C50BBB18C0BA');
        $this->addSql('DROP INDEX uniq_5af1c50bbb18c0ba');
        $this->addSql('ALTER TABLE verification ALTER poa_id TYPE BIGINT');
        $this->addSql('ALTER TABLE verification ALTER poa_id DROP DEFAULT');
        $this->addSql('ALTER TABLE verification ADD CONSTRAINT FK_5AF1C50BBB18C0BA FOREIGN KEY (poa_id) REFERENCES claim (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_5AF1C50BBB18C0BA ON verification (poa_id)');
        $this->addSql('ALTER TABLE poa ADD case_number VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE poa ADD original_payment_amount VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE poa DROP net_amount_paid');
        $this->addSql('ALTER TABLE poa DROP amount_to_refund');
        $this->addSql('ALTER TABLE poa ALTER received_datetime TYPE DATE');
        $this->addSql('ALTER TABLE poa ALTER received_datetime DROP DEFAULT');
        $this->addSql('ALTER TABLE poa RENAME COLUMN status TO system');
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
        $this->addSql('DROP INDEX IDX_5AF1C50BBB18C0BA');
        $this->addSql('ALTER TABLE verification ALTER poa_id TYPE INT');
        $this->addSql('ALTER TABLE verification ALTER poa_id DROP DEFAULT');
        $this->addSql('ALTER TABLE verification ADD CONSTRAINT fk_5af1c50bbb18c0ba FOREIGN KEY (poa_id) REFERENCES poa (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_5af1c50bbb18c0ba ON verification (poa_id)');
        $this->addSql('ALTER TABLE poa ADD net_amount_paid NUMERIC(10, 0) NOT NULL');
        $this->addSql('ALTER TABLE poa ADD status VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE poa ADD amount_to_refund NUMERIC(10, 0) NOT NULL');
        $this->addSql('ALTER TABLE poa DROP system');
        $this->addSql('ALTER TABLE poa DROP case_number');
        $this->addSql('ALTER TABLE poa DROP original_payment_amount');
        $this->addSql('ALTER TABLE poa ALTER received_datetime TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE poa ALTER received_datetime DROP DEFAULT');
    }
}
