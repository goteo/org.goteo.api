<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251126132140 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE project_update ADD author_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE project_update ADD CONSTRAINT FK_8F81DE32F675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_8F81DE32F675F31B ON project_update (author_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE project_update DROP FOREIGN KEY FK_8F81DE32F675F31B');
        $this->addSql('DROP INDEX IDX_8F81DE32F675F31B ON project_update');
        $this->addSql('ALTER TABLE project_update DROP author_id');
    }
}
