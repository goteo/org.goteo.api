<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260206141942 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds links column to project and user tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE project ADD links JSON DEFAULT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE user ADD links JSON DEFAULT NULL COMMENT \'(DC2Type:json)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user DROP links');
        $this->addSql('ALTER TABLE project DROP links');
    }
}
