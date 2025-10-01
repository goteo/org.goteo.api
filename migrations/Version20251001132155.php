<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251001132155 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Set and populate the `category` and `project_category` fields.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE category (
              id VARCHAR(255) NOT NULL,
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE project_category (
              project_id INT NOT NULL,
              category_id VARCHAR(255) NOT NULL,
              INDEX IDX_3B02921A166D1F9C (project_id),
              INDEX IDX_3B02921A12469DE2 (category_id),
              PRIMARY KEY(project_id, category_id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              project_category
            ADD
              CONSTRAINT FK_3B02921A166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              project_category
            ADD
              CONSTRAINT FK_3B02921A12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE project DROP category
        SQL);

        $this->addSql(<<<'SQL'
            INSERT INTO category (id) VALUES ('education');
            INSERT INTO category (id) VALUES ('culture');
            INSERT INTO category (id) VALUES ('journalism');
            INSERT INTO category (id) VALUES ('democracy');
            INSERT INTO category (id) VALUES ('rural-development');
            INSERT INTO category (id) VALUES ('health-and-cares');
            INSERT INTO category (id) VALUES ('science-and-technology');
            INSERT INTO category (id) VALUES ('sustainability');
            INSERT INTO category (id) VALUES ('social-economy');
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE project_category DROP FOREIGN KEY FK_3B02921A166D1F9C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE project_category DROP FOREIGN KEY FK_3B02921A12469DE2
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE category
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE project_category
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE project ADD category VARCHAR(255) NOT NULL
        SQL);
    }
}
