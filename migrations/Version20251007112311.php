<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251007112311 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Project Collaborations. Table and relationship setup.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE project_collaboration (id INT AUTO_INCREMENT NOT NULL, project_id INT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, is_fulfilled TINYINT(1) NOT NULL, locales JSON NOT NULL COMMENT '(DC2Type:json)', INDEX IDX_EAB7425166D1F9C (project_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE project_collaboration ADD CONSTRAINT FK_EAB7425166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE project_collaboration DROP FOREIGN KEY FK_EAB7425166D1F9C
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE project_collaboration
        SQL);
    }
}
