<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260208130614 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make ProjectVideo fields store as TEXT';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE project CHANGE video_src video_src TEXT DEFAULT NULL, CHANGE video_thumbnail video_thumbnail TEXT DEFAULT NULL, CHANGE video_cover video_cover TEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE project CHANGE video_src video_src VARCHAR(255) DEFAULT NULL, CHANGE video_cover video_cover VARCHAR(255) DEFAULT NULL, CHANGE video_thumbnail video_thumbnail VARCHAR(255) DEFAULT NULL');
    }
}
