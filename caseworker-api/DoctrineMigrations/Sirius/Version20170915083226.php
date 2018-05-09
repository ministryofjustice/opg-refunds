<?php

namespace DoctrineMigrations\Sirius;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170915083226 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs

        $fullUsername = getenv('OPG_REFUNDS_DB_SIRIUS_FULL_USERNAME');
        $this->addSql("GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO $fullUsername");
        $this->addSql("GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO $fullUsername");

        $migrationUsername = getenv('OPG_REFUNDS_DB_SIRIUS_MIGRATION_USERNAME');
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
    }
}
