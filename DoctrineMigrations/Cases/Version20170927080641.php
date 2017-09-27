<?php

namespace DoctrineMigrations\Cases;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170927080641 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        // Additional Test Caseworkers
        $this->addSql('INSERT INTO "user" (name, email, password_hash, status, roles) VALUES (\'Case Worker 01\', \'caseworker01@refund.uat\', \'bd94dcda26fccb4e68d6a31f9b5aac0b571ae266d822620e901ef7ebe3a11d4f\', \'active\', \'Caseworker\')');
        $this->addSql('INSERT INTO "user" (name, email, password_hash, status, roles) VALUES (\'Case Worker 02\', \'caseworker02@refund.uat\', \'bd94dcda26fccb4e68d6a31f9b5aac0b571ae266d822620e901ef7ebe3a11d4f\', \'active\', \'Caseworker\')');
        $this->addSql('INSERT INTO "user" (name, email, password_hash, status, roles) VALUES (\'Case Worker 03\', \'caseworker03@refund.uat\', \'bd94dcda26fccb4e68d6a31f9b5aac0b571ae266d822620e901ef7ebe3a11d4f\', \'active\', \'Caseworker\')');
        $this->addSql('INSERT INTO "user" (name, email, password_hash, status, roles) VALUES (\'Case Worker 04\', \'caseworker04@refund.uat\', \'bd94dcda26fccb4e68d6a31f9b5aac0b571ae266d822620e901ef7ebe3a11d4f\', \'active\', \'Caseworker\')');
        $this->addSql('INSERT INTO "user" (name, email, password_hash, status, roles) VALUES (\'Case Worker 05\', \'caseworker05@refund.uat\', \'bd94dcda26fccb4e68d6a31f9b5aac0b571ae266d822620e901ef7ebe3a11d4f\', \'active\', \'Caseworker\')');
        $this->addSql('INSERT INTO "user" (name, email, password_hash, status, roles) VALUES (\'Reporting 01\', \'reporting01@refund.uat\', \'bd94dcda26fccb4e68d6a31f9b5aac0b571ae266d822620e901ef7ebe3a11d4f\', \'active\', \'Reporting,Caseworker\')');
        $this->addSql('INSERT INTO "user" (name, email, password_hash, status, roles) VALUES (\'Reporting 02\', \'reporting02@refund.uat\', \'bd94dcda26fccb4e68d6a31f9b5aac0b571ae266d822620e901ef7ebe3a11d4f\', \'active\', \'Reporting,Caseworker\')');
        $this->addSql('INSERT INTO "user" (name, email, password_hash, status, roles) VALUES (\'Refund Manager 01\', \'refundmanager01@refund.uat\', \'bd94dcda26fccb4e68d6a31f9b5aac0b571ae266d822620e901ef7ebe3a11d4f\', \'active\', \'RefundManager,Caseworker\')');
        $this->addSql('INSERT INTO "user" (name, email, password_hash, status, roles) VALUES (\'Refund Manager 02\', \'refundmanager02@refund.uat\', \'bd94dcda26fccb4e68d6a31f9b5aac0b571ae266d822620e901ef7ebe3a11d4f\', \'active\', \'RefundManager,Caseworker\')');
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
