<?php

namespace DoctrineMigrations\Cases;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170912131142 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP SEQUENCE cases_id_seq CASCADE');
        $this->addSql('ALTER TABLE verification ALTER case_id TYPE BIGINT');
        $this->addSql('ALTER TABLE verification ALTER case_id DROP DEFAULT');
        $this->addSql('ALTER TABLE log ALTER case_id TYPE BIGINT');
        $this->addSql('ALTER TABLE log ALTER case_id DROP DEFAULT');
        $this->addSql('ALTER TABLE cases ALTER id TYPE BIGINT');
        $this->addSql('ALTER TABLE cases ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE cases ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE cases ALTER json_data TYPE BYTEA USING json_data::bytea');
        $this->addSql('ALTER TABLE cases ALTER json_data DROP DEFAULT');
        $this->addSql('ALTER TABLE cases ALTER status TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE cases ALTER status DROP DEFAULT');
        $this->addSql('ALTER TABLE payment ALTER case_id TYPE BIGINT');
        $this->addSql('ALTER TABLE payment ALTER case_id DROP DEFAULT');
        $this->addSql('ALTER TABLE poa ALTER case_id TYPE BIGINT');
        $this->addSql('ALTER TABLE poa ALTER case_id DROP DEFAULT');
        $this->addSql('ALTER TABLE poa ALTER status TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE poa ALTER status DROP DEFAULT');
        $this->addSql('ALTER TABLE caseworker ALTER status TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE caseworker ALTER status DROP DEFAULT');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE cases_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('ALTER TABLE verification ALTER case_id TYPE INT');
        $this->addSql('ALTER TABLE verification ALTER case_id DROP DEFAULT');
        $this->addSql('ALTER TABLE log ALTER case_id TYPE INT');
        $this->addSql('ALTER TABLE log ALTER case_id DROP DEFAULT');
        $this->addSql('ALTER TABLE caseworker ALTER status TYPE INT');
        $this->addSql('ALTER TABLE caseworker ALTER status DROP DEFAULT');
        $this->addSql('ALTER TABLE payment ALTER case_id TYPE INT');
        $this->addSql('ALTER TABLE payment ALTER case_id DROP DEFAULT');
        $this->addSql('ALTER TABLE cases ALTER id TYPE SERIAL');
        $this->addSql('ALTER TABLE cases ALTER id DROP DEFAULT');
        $this->addSql('CREATE SEQUENCE cases_id_seq');
        $this->addSql('SELECT setval(\'cases_id_seq\', (SELECT MAX(id) FROM cases))');
        $this->addSql('ALTER TABLE cases ALTER id SET DEFAULT nextval(\'cases_id_seq\')');
        $this->addSql('ALTER TABLE cases ALTER json_data TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE cases ALTER json_data DROP DEFAULT');
        $this->addSql('ALTER TABLE cases ALTER status TYPE INT');
        $this->addSql('ALTER TABLE cases ALTER status DROP DEFAULT');
        $this->addSql('ALTER TABLE poa ALTER case_id TYPE INT');
        $this->addSql('ALTER TABLE poa ALTER case_id DROP DEFAULT');
        $this->addSql('ALTER TABLE poa ALTER status TYPE INT');
        $this->addSql('ALTER TABLE poa ALTER status DROP DEFAULT');
    }
}
