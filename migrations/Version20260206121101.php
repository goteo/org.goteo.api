<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260206121101 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds video_cover column';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE project ADD video_cover VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE project DROP video_cover');
    }
}
