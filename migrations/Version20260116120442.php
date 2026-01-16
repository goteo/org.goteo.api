<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260116120442 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Index on checkout with gateway names';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE INDEX IDX_AF382D4E3D4E91C8BF396750 ON checkout (gateway_name, id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_AF382D4E3D4E91C8BF396750 ON checkout');
    }
}
