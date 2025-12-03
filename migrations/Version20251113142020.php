<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251113142020 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update project statuses';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("UPDATE project SET `status` = 'funding.paid' WHERE `status` = 'funded'");
        $this->addSql("UPDATE project SET `status` = 'in_campaign_review.request_change' WHERE `status` = 'in_editing'");
        $this->addSql("UPDATE project SET `status` = 'in_campaign_review' WHERE `status` = 'in_review'");
        $this->addSql("UPDATE project SET `status` = 'campaign.failed' WHERE `status` = 'unfunded'");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("UPDATE project SET `status` = 'funded' WHERE `status` = 'funding.paid'");
        $this->addSql("UPDATE project SET `status` = 'in_editing' WHERE `status` = 'in_campaign_review.request_change'");
        $this->addSql("UPDATE project SET `status` = 'in_review' WHERE `status` = 'in_campaign_review'");
        $this->addSql("UPDATE project SET `status` = 'unfunded' WHERE `status` = 'campaign.failed'");
    }
}
