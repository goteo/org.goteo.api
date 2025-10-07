<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251007115926 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Timestamps. Add create and update timestamps to Project Collaborations and Rewards.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE project_collaboration ADD date_created DATETIME NOT NULL, ADD date_updated DATETIME NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE project_reward ADD date_created DATETIME NOT NULL, ADD date_updated DATETIME NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE project_reward DROP date_created, DROP date_updated
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE project_collaboration DROP date_created, DROP date_updated
        SQL);
    }
}
