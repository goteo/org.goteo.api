<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251007164144 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE category (id VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE project_category (project_id INT NOT NULL, category_id VARCHAR(255) NOT NULL, INDEX IDX_3B02921A166D1F9C (project_id), INDEX IDX_3B02921A12469DE2 (category_id), PRIMARY KEY(project_id, category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE project_collaboration (id INT AUTO_INCREMENT NOT NULL, project_id INT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, is_fulfilled TINYINT(1) NOT NULL, locales JSON NOT NULL COMMENT \'(DC2Type:json)\', date_created DATETIME NOT NULL, date_updated DATETIME NOT NULL, INDEX IDX_EAB7425166D1F9C (project_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE project_category ADD CONSTRAINT FK_3B02921A166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE project_category ADD CONSTRAINT FK_3B02921A12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE project_collaboration ADD CONSTRAINT FK_EAB7425166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');
        $this->addSql('ALTER TABLE match_call CHANGE territory_country territory_country VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE project DROP category, CHANGE description description LONGTEXT DEFAULT NULL, CHANGE territory_country territory_country VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE project_reward ADD date_created DATETIME NOT NULL, ADD date_updated DATETIME NOT NULL');

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
        $this->addSql('ALTER TABLE project_category DROP FOREIGN KEY FK_3B02921A166D1F9C');
        $this->addSql('ALTER TABLE project_category DROP FOREIGN KEY FK_3B02921A12469DE2');
        $this->addSql('ALTER TABLE project_collaboration DROP FOREIGN KEY FK_EAB7425166D1F9C');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE project_category');
        $this->addSql('DROP TABLE project_collaboration');
        $this->addSql('ALTER TABLE project_reward DROP date_created, DROP date_updated');
        $this->addSql('ALTER TABLE match_call CHANGE territory_country territory_country VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE project ADD category VARCHAR(255) NOT NULL, CHANGE description description LONGTEXT NOT NULL, CHANGE territory_country territory_country VARCHAR(255) NOT NULL');
    }
}
