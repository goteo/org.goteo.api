<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251126132140 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds author_id column to the project_update table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE project_update ADD author_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE project_update ADD CONSTRAINT FK_8F81DE32F675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_8F81DE32F675F31B ON project_update (author_id)');

        $this->addSql('UPDATE project_update pu
            JOIN project p ON p.id = pu.project_id
            SET pu.author_id = p.owner_id
            WHERE pu.body != ""
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE project_update DROP FOREIGN KEY FK_8F81DE32F675F31B');
        $this->addSql('DROP INDEX IDX_8F81DE32F675F31B ON project_update');
        $this->addSql('ALTER TABLE project_update DROP author_id');
    }
}
