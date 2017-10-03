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

        $this->addSql('UPDATE "user" SET email = \'caseworker01@publicguardian.gsi.gov.uk\' WHERE email = \'caseworker01@refunds.uat\'');
        $this->addSql('UPDATE "user" SET email = \'caseworker02@publicguardian.gsi.gov.uk\' WHERE email = \'caseworker02@refunds.uat\'');
        $this->addSql('UPDATE "user" SET email = \'caseworker03@publicguardian.gsi.gov.uk\' WHERE email = \'caseworker03@refunds.uat\'');
        $this->addSql('UPDATE "user" SET email = \'caseworker04@publicguardian.gsi.gov.uk\' WHERE email = \'caseworker04@refunds.uat\'');
        $this->addSql('UPDATE "user" SET email = \'caseworker05@publicguardian.gsi.gov.uk\' WHERE email = \'caseworker05@refunds.uat\'');
        $this->addSql('UPDATE "user" SET email = \'caseworker06@publicguardian.gsi.gov.uk\' WHERE email = \'caseworker06@refunds.uat\'');
        $this->addSql('UPDATE "user" SET email = \'caseworker07@publicguardian.gsi.gov.uk\' WHERE email = \'caseworker07@refunds.uat\'');
        $this->addSql('UPDATE "user" SET email = \'caseworker08@publicguardian.gsi.gov.uk\' WHERE email = \'caseworker08@refunds.uat\'');
        $this->addSql('UPDATE "user" SET email = \'caseworker09@publicguardian.gsi.gov.uk\' WHERE email = \'caseworker09@refunds.uat\'');
        $this->addSql('UPDATE "user" SET email = \'caseworker10@publicguardian.gsi.gov.uk\' WHERE email = \'caseworker10@refunds.uat\'');
        $this->addSql('UPDATE "user" SET email = \'refundmanager01@publicguardian.gsi.gov.uk\' WHERE email = \'refundmanager01@refunds.uat\'');
        $this->addSql('UPDATE "user" SET email = \'refundmanager02@publicguardian.gsi.gov.uk\' WHERE email = \'refundmanager02@refunds.uat\'');
        $this->addSql('UPDATE "user" SET email = \'refundmanager03@publicguardian.gsi.gov.uk\' WHERE email = \'refundmanager03@refunds.uat\'');
        $this->addSql('UPDATE "user" SET email = \'refundmanager04@publicguardian.gsi.gov.uk\' WHERE email = \'refundmanager04@refunds.uat\'');
        $this->addSql('UPDATE "user" SET email = \'refundmanager05@publicguardian.gsi.gov.uk\' WHERE email = \'refundmanager05@refunds.uat\'');

        //  Create admin user
        $adminName = getenv('OPG_REFUNDS_CASEWORKER_ADMIN_NAME');
        $adminUsername = getenv('OPG_REFUNDS_CASEWORKER_ADMIN_USERNAME');
        $adminPasswordHash = getenv('OPG_REFUNDS_CASEWORKER_ADMIN_PASSWORD_HASH');
        $this->addSql("INSERT INTO \"user\" (name, email, password_hash, status, roles) VALUES ('$adminName', '$adminUsername', '$adminPasswordHash', 'active', 'RefundManager,Caseworker,Reporting,Admin')");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('UPDATE "user" SET email = \'caseworker01@publicguardian.gsi.gov.uk\' WHERE email = \'caseworker01@refunds.uat\'');
        $this->addSql('UPDATE "user" SET email = \'caseworker02@publicguardian.gsi.gov.uk\' WHERE email = \'caseworker02@refunds.uat\'');
        $this->addSql('UPDATE "user" SET email = \'caseworker03@publicguardian.gsi.gov.uk\' WHERE email = \'caseworker03@refunds.uat\'');
        $this->addSql('UPDATE "user" SET email = \'caseworker04@publicguardian.gsi.gov.uk\' WHERE email = \'caseworker04@refunds.uat\'');
        $this->addSql('UPDATE "user" SET email = \'caseworker05@publicguardian.gsi.gov.uk\' WHERE email = \'caseworker05@refunds.uat\'');
        $this->addSql('UPDATE "user" SET email = \'caseworker06@publicguardian.gsi.gov.uk\' WHERE email = \'caseworker06@refunds.uat\'');
        $this->addSql('UPDATE "user" SET email = \'caseworker07@publicguardian.gsi.gov.uk\' WHERE email = \'caseworker07@refunds.uat\'');
        $this->addSql('UPDATE "user" SET email = \'caseworker08@publicguardian.gsi.gov.uk\' WHERE email = \'caseworker08@refunds.uat\'');
        $this->addSql('UPDATE "user" SET email = \'caseworker09@publicguardian.gsi.gov.uk\' WHERE email = \'caseworker09@refunds.uat\'');
        $this->addSql('UPDATE "user" SET email = \'caseworker10@publicguardian.gsi.gov.uk\' WHERE email = \'caseworker10@refunds.uat\'');
        $this->addSql('UPDATE "user" SET email = \'refundmanager01@publicguardian.gsi.gov.uk\' WHERE email = \'refundmanager01@refunds.uat\'');
        $this->addSql('UPDATE "user" SET email = \'refundmanager02@publicguardian.gsi.gov.uk\' WHERE email = \'refundmanager02@refunds.uat\'');
        $this->addSql('UPDATE "user" SET email = \'refundmanager03@publicguardian.gsi.gov.uk\' WHERE email = \'refundmanager03@refunds.uat\'');
        $this->addSql('UPDATE "user" SET email = \'refundmanager04@publicguardian.gsi.gov.uk\' WHERE email = \'refundmanager04@refunds.uat\'');
        $this->addSql('UPDATE "user" SET email = \'refundmanager05@publicguardian.gsi.gov.uk\' WHERE email = \'refundmanager05@refunds.uat\'');

        $this->addSql('DELETE FROM "user" WHERE email=\'smtuser01@publicguardian.gsi.gov.uk\'');
    }
}
