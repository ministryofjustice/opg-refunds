<?php

namespace DoctrineMigrations\Cases;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171005171046 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE log RENAME TO note');
        $this->addSql('DROP SEQUENCE log_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE note_id_seq');
        $this->addSql('SELECT setval(\'note_id_seq\', (SELECT MAX(id) FROM note))');
        $this->addSql('ALTER TABLE note ALTER id SET DEFAULT nextval(\'note_id_seq\')');
        $this->addSql('ALTER INDEX idx_8f3f68c57096a49f RENAME TO IDX_CFBDFA147096A49F');
        $this->addSql('ALTER INDEX idx_8f3f68c5a76ed395 RENAME TO IDX_CFBDFA14A76ED395');
        $this->addSql('ALTER INDEX idx_8f3f68c5bb18c0ba RENAME TO IDX_CFBDFA14BB18C0BA');

        // Grant access to users
        $fullUsername = getenv('OPG_REFUNDS_DB_CASES_FULL_USERNAME');
        $this->addSql("GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO $fullUsername");
        $this->addSql("GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO $fullUsername");

        $migrationUsername = getenv('OPG_REFUNDS_DB_CASES_MIGRATION_USERNAME');
        $this->addSql("GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO $migrationUsername");
        $this->addSql("GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO $migrationUsername");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE note RENAME TO log');
        $this->addSql('DROP SEQUENCE note_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE log_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('SELECT setval(\'log_id_seq\', (SELECT MAX(id) FROM log))');
        $this->addSql('ALTER TABLE log ALTER id SET DEFAULT nextval(\'log_id_seq\')');
        $this->addSql('ALTER INDEX idx_cfbdfa14bb18c0ba RENAME TO idx_8f3f68c5bb18c0ba');
        $this->addSql('ALTER INDEX idx_cfbdfa147096a49f RENAME TO idx_8f3f68c57096a49f');
        $this->addSql('ALTER INDEX idx_cfbdfa14a76ed395 RENAME TO idx_8f3f68c5a76ed395');

        // Grant access to users
        $fullUsername = getenv('OPG_REFUNDS_DB_CASES_FULL_USERNAME');
        $this->addSql("GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO $fullUsername");
        $this->addSql("GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO $fullUsername");

        $migrationUsername = getenv('OPG_REFUNDS_DB_CASES_MIGRATION_USERNAME');
        $this->addSql("GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO $migrationUsername");
        $this->addSql("GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO $migrationUsername");
    }
}
