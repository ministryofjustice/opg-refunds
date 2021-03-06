<?php

namespace DoctrineMigrations\Cases;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171002122206 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('UPDATE "user" SET email = \'caseworker01@publicguardian.gov.uk\' WHERE email = \'caseworker01@refunds.uat\'');
        $this->addSql('UPDATE "user" SET email = \'caseworker02@publicguardian.gov.uk\' WHERE email = \'caseworker02@refunds.uat\'');
        $this->addSql('UPDATE "user" SET email = \'caseworker03@publicguardian.gov.uk\' WHERE email = \'caseworker03@refunds.uat\'');
        $this->addSql('UPDATE "user" SET email = \'caseworker04@publicguardian.gov.uk\' WHERE email = \'caseworker04@refunds.uat\'');
        $this->addSql('UPDATE "user" SET email = \'caseworker05@publicguardian.gov.uk\' WHERE email = \'caseworker05@refunds.uat\'');
        $this->addSql('UPDATE "user" SET email = \'caseworker06@publicguardian.gov.uk\' WHERE email = \'caseworker06@refunds.uat\'');
        $this->addSql('UPDATE "user" SET email = \'caseworker07@publicguardian.gov.uk\' WHERE email = \'caseworker07@refunds.uat\'');
        $this->addSql('UPDATE "user" SET email = \'caseworker08@publicguardian.gov.uk\' WHERE email = \'caseworker08@refunds.uat\'');
        $this->addSql('UPDATE "user" SET email = \'caseworker09@publicguardian.gov.uk\' WHERE email = \'caseworker09@refunds.uat\'');
        $this->addSql('UPDATE "user" SET email = \'caseworker10@publicguardian.gov.uk\' WHERE email = \'caseworker10@refunds.uat\'');
        $this->addSql('UPDATE "user" SET email = \'refundmanager01@publicguardian.gov.uk\' WHERE email = \'refundmanager01@refunds.uat\'');
        $this->addSql('UPDATE "user" SET email = \'refundmanager02@publicguardian.gov.uk\' WHERE email = \'refundmanager02@refunds.uat\'');
        $this->addSql('UPDATE "user" SET email = \'refundmanager03@publicguardian.gov.uk\' WHERE email = \'refundmanager03@refunds.uat\'');
        $this->addSql('UPDATE "user" SET email = \'refundmanager04@publicguardian.gov.uk\' WHERE email = \'refundmanager04@refunds.uat\'');
        $this->addSql('UPDATE "user" SET email = \'refundmanager05@publicguardian.gov.uk\' WHERE email = \'refundmanager05@refunds.uat\'');

        //  Create admin user
        $adminName = getenv('OPG_REFUNDS_CASEWORKER_ADMIN_NAME');
        $adminUsername = getenv('OPG_REFUNDS_CASEWORKER_ADMIN_USERNAME');
        $adminPassword = getenv('OPG_REFUNDS_CASEWORKER_ADMIN_PASSWORD');
        $adminPasswordHash = password_hash($adminPassword, PASSWORD_DEFAULT);
        $this->addSql("INSERT INTO \"user\" (name, email, password_hash, status, roles) VALUES ('$adminName', '$adminUsername', '$adminPasswordHash', 'active', 'RefundManager,Caseworker,Reporting,Admin')");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('UPDATE "user" SET email = \'caseworker01@publicguardian.gov.uk\' WHERE email = \'caseworker01@refunds.uat\'');
        $this->addSql('UPDATE "user" SET email = \'caseworker02@publicguardian.gov.uk\' WHERE email = \'caseworker02@refunds.uat\'');
        $this->addSql('UPDATE "user" SET email = \'caseworker03@publicguardian.gov.uk\' WHERE email = \'caseworker03@refunds.uat\'');
        $this->addSql('UPDATE "user" SET email = \'caseworker04@publicguardian.gov.uk\' WHERE email = \'caseworker04@refunds.uat\'');
        $this->addSql('UPDATE "user" SET email = \'caseworker05@publicguardian.gov.uk\' WHERE email = \'caseworker05@refunds.uat\'');
        $this->addSql('UPDATE "user" SET email = \'caseworker06@publicguardian.gov.uk\' WHERE email = \'caseworker06@refunds.uat\'');
        $this->addSql('UPDATE "user" SET email = \'caseworker07@publicguardian.gov.uk\' WHERE email = \'caseworker07@refunds.uat\'');
        $this->addSql('UPDATE "user" SET email = \'caseworker08@publicguardian.gov.uk\' WHERE email = \'caseworker08@refunds.uat\'');
        $this->addSql('UPDATE "user" SET email = \'caseworker09@publicguardian.gov.uk\' WHERE email = \'caseworker09@refunds.uat\'');
        $this->addSql('UPDATE "user" SET email = \'caseworker10@publicguardian.gov.uk\' WHERE email = \'caseworker10@refunds.uat\'');
        $this->addSql('UPDATE "user" SET email = \'refundmanager01@publicguardian.gov.uk\' WHERE email = \'refundmanager01@refunds.uat\'');
        $this->addSql('UPDATE "user" SET email = \'refundmanager02@publicguardian.gov.uk\' WHERE email = \'refundmanager02@refunds.uat\'');
        $this->addSql('UPDATE "user" SET email = \'refundmanager03@publicguardian.gov.uk\' WHERE email = \'refundmanager03@refunds.uat\'');
        $this->addSql('UPDATE "user" SET email = \'refundmanager04@publicguardian.gov.uk\' WHERE email = \'refundmanager04@refunds.uat\'');
        $this->addSql('UPDATE "user" SET email = \'refundmanager05@publicguardian.gov.uk\' WHERE email = \'refundmanager05@refunds.uat\'');

        $this->addSql('DELETE FROM "user" WHERE email=\'smtuser01@publicguardian.gov.uk\'');
    }
}
