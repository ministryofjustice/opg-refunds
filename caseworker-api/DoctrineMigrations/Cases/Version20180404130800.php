<?php declare(strict_types = 1);

namespace DoctrineMigrations\Cases;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180404130800 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE payment ADD claim_id BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D7096A49F FOREIGN KEY (claim_id) REFERENCES claim (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6D28840D7096A49F ON payment (claim_id)');

        $this->addSql('UPDATE payment p SET claim_id = (SELECT c.id from claim c where c.payment_id = p.id);');

        $this->addSql('ALTER TABLE claim DROP CONSTRAINT fk_a769de274c3a3bb');
        $this->addSql('DROP INDEX uniq_a769de274c3a3bb');
        $this->addSql('ALTER TABLE claim DROP payment_id');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE claim ADD payment_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE claim ADD CONSTRAINT fk_a769de274c3a3bb FOREIGN KEY (payment_id) REFERENCES payment (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_a769de274c3a3bb ON claim (payment_id)');

        $this->addSql('UPDATE claim c SET payment_id = (SELECT p.id from payment p where p.claim_id = c.id);');

        $this->addSql('ALTER TABLE payment DROP CONSTRAINT FK_6D28840D7096A49F');
        $this->addSql('DROP INDEX UNIQ_6D28840D7096A49F');
        $this->addSql('ALTER TABLE payment DROP claim_id');
    }
}
