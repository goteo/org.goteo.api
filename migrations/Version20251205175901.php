<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251205175901 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update charge and checkout statuses';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE checkout SET `status` = 'to_charge' WHERE `status` = 'in_pending'");
        $this->addSql("UPDATE checkout_charge SET `status` = 'to_charge' WHERE `status` = 'in_pending'");
        $this->addSql("UPDATE checkout_charge SET `status` = 'in_charge' WHERE `status` = 'charged'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("UPDATE checkout SET `status` = 'in_pending' WHERE `status` = 'to_charge'");
        $this->addSql("UPDATE checkout_charge SET `status` = 'in_pending' WHERE `status` = 'to_charge'");
        $this->addSql("UPDATE checkout_charge SET `status` = 'charged' WHERE `status` = 'in_charge'");
    }
}
