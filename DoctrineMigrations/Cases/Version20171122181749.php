<?php

namespace DoctrineMigrations\Cases;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171122181749 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE claim ADD outcome_email_sent BOOLEAN DEFAULT \'false\' NOT NULL');
        $this->addSql('ALTER TABLE claim ADD outcome_text_sent BOOLEAN DEFAULT \'false\' NOT NULL');

        $this->addSql('UPDATE claim SET outcome_email_sent = true WHERE status IN (\'rejected\', \'accepted\') AND json_data->\'contact\'->\'email\' IS NOT NULL');
        $this->addSql('UPDATE claim SET outcome_text_sent = true WHERE status IN (\'rejected\', \'accepted\') AND json_data->\'contact\'->\'phone\' IS NOT NULL AND (json_data->>\'contact\')::json->>\'phone\' LIKE \'07%\'');

        $this->addSql('INSERT INTO note (claim_id, user_id, created_datetime, type, message) (
                              SELECT id, finished_by_id, finished_datetime, \'claim_rejected_email_sent\', \'Successfully sent rejection email to \' || replace((json_data->\'contact\'->\'email\')::TEXT, \'"\', \'\') FROM claim
                                WHERE status = \'rejected\' AND json_data->\'contact\'->\'email\' IS NOT NULL
                            )');

        $this->addSql('INSERT INTO note (claim_id, user_id, created_datetime, type, message) (
                              SELECT id, finished_by_id, finished_datetime, \'claim_rejected_text_sent\', \'Successfully sent rejection text to \' || replace((json_data->\'contact\'->\'phone\')::TEXT, \'"\', \'\') FROM claim
                                WHERE status = \'rejected\' AND json_data->\'contact\'->\'phone\' IS NOT NULL AND (json_data->>\'contact\')::JSON->>\'mobile\' LIKE \'07%\'
                            )');

        $this->addSql('INSERT INTO note (claim_id, user_id, created_datetime, type, message) (
                              SELECT id, finished_by_id, finished_datetime, \'claim_accepted_email_sent\', \'Successfully sent acceptance email to \' || replace((json_data->\'contact\'->\'email\')::TEXT, \'"\', \'\') FROM claim
                                WHERE status = \'accepted\' AND json_data->\'contact\'->\'email\' IS NOT NULL
                            )');

        $this->addSql('INSERT INTO note (claim_id, user_id, created_datetime, type, message) (
                              SELECT id, finished_by_id, finished_datetime, \'claim_accepted_text_sent\', \'Successfully sent acceptance text to \' || replace((json_data->\'contact\'->\'phone\')::TEXT, \'"\', \'\') FROM claim
                                WHERE status = \'accepted\' AND json_data->\'contact\'->\'phone\' IS NOT NULL AND (json_data->>\'contact\')::JSON->>\'phone\' LIKE \'07%\'
                            )');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE claim DROP outcome_email_sent');
        $this->addSql('ALTER TABLE claim DROP outcome_text_sent');
    }
}
