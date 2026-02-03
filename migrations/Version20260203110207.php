<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260203110207 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Schema setup';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE accounting (id INT AUTO_INCREMENT NOT NULL, currency VARCHAR(3) NOT NULL, owner_class VARCHAR(255) NOT NULL, balance_amount INT DEFAULT NULL, balance_currency VARCHAR(255) DEFAULT NULL, balance_conversion JSON DEFAULT NULL COMMENT \'(DC2Type:json)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE accounting_transaction (id INT AUTO_INCREMENT NOT NULL, origin_id INT NOT NULL, target_id INT NOT NULL, date_created DATETIME NOT NULL, money_amount INT DEFAULT NULL, money_currency VARCHAR(255) DEFAULT NULL, money_conversion JSON DEFAULT NULL COMMENT \'(DC2Type:json)\', INDEX IDX_E88BF0156A273CC (origin_id), INDEX IDX_E88BF01158E0B66 (target_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE category (id VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE checkout (id INT AUTO_INCREMENT NOT NULL, origin_id INT NOT NULL, gateway_name VARCHAR(255) NOT NULL, refund_strategy VARCHAR(255) NOT NULL, return_url LONGTEXT NOT NULL, status VARCHAR(255) NOT NULL, links JSON NOT NULL COMMENT \'(DC2Type:json)\', migrated TINYINT(1) NOT NULL, migrated_id VARCHAR(255) DEFAULT NULL, date_created DATETIME NOT NULL, date_updated DATETIME NOT NULL, INDEX IDX_AF382D4E56A273CC (origin_id), INDEX IDX_AF382D4ECAA6B324 (migrated_id), INDEX IDX_AF382D4E3D4E91C8BF396750 (gateway_name, id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE checkout_charge (id INT AUTO_INCREMENT NOT NULL, checkout_id INT DEFAULT NULL, target_id INT NOT NULL, type VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, status VARCHAR(255) NOT NULL, migrated TINYINT(1) NOT NULL, migrated_id VARCHAR(255) DEFAULT NULL, date_created DATETIME NOT NULL, date_updated DATETIME NOT NULL, money_amount INT DEFAULT NULL, money_currency VARCHAR(255) DEFAULT NULL, money_conversion JSON DEFAULT NULL COMMENT \'(DC2Type:json)\', INDEX IDX_8707A16F146D8724 (checkout_id), INDEX IDX_8707A16F158E0B66 (target_id), INDEX IDX_8707A16FCAA6B324 (migrated_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE checkout_charge_trxs (charge_id INT NOT NULL, transaction_id INT NOT NULL, INDEX IDX_7E9B717F55284914 (charge_id), INDEX IDX_7E9B717F2FC0CB0F (transaction_id), PRIMARY KEY(charge_id, transaction_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE checkout_tracking (id INT AUTO_INCREMENT NOT NULL, checkout_id INT NOT NULL, title VARCHAR(255) NOT NULL, value VARCHAR(255) NOT NULL, INDEX IDX_8159C060146D8724 (checkout_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ext_log_entries (id INT AUTO_INCREMENT NOT NULL, action VARCHAR(8) NOT NULL, logged_at DATETIME NOT NULL, object_id VARCHAR(64) DEFAULT NULL, object_class VARCHAR(191) NOT NULL, version INT NOT NULL, data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', username VARCHAR(191) DEFAULT NULL, INDEX log_class_lookup_idx (object_class), INDEX log_date_lookup_idx (logged_at), INDEX log_user_lookup_idx (username), INDEX log_version_lookup_idx (object_id, object_class, version), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB ROW_FORMAT = DYNAMIC');
        $this->addSql('CREATE TABLE ext_translations (id INT AUTO_INCREMENT NOT NULL, locale VARCHAR(8) NOT NULL, object_class VARCHAR(191) NOT NULL, field VARCHAR(32) NOT NULL, foreign_key VARCHAR(64) NOT NULL, content LONGTEXT DEFAULT NULL, INDEX translations_lookup_idx (locale, object_class, foreign_key), INDEX general_translations_lookup_idx (object_class, foreign_key), UNIQUE INDEX lookup_unique_idx (locale, object_class, field, foreign_key), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB ROW_FORMAT = DYNAMIC');
        $this->addSql('CREATE TABLE match_call (id INT AUTO_INCREMENT NOT NULL, accounting_id INT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, status VARCHAR(255) NOT NULL, territory_country VARCHAR(255) DEFAULT NULL, territory_sub_lvl1 VARCHAR(255) DEFAULT NULL, territory_sub_lvl2 VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_ADE19E4C3B7DD068 (accounting_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE match_call_user (match_call_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_A3A3FDBE270EC443 (match_call_id), INDEX IDX_A3A3FDBEA76ED395 (user_id), PRIMARY KEY(match_call_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE match_call_submission (id INT AUTO_INCREMENT NOT NULL, call_id INT NOT NULL, project_id INT NOT NULL, status VARCHAR(255) NOT NULL, INDEX IDX_A77BFE0C50A89B2C (call_id), INDEX IDX_A77BFE0C166D1F9C (project_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE match_strategy (id INT AUTO_INCREMENT NOT NULL, call_id INT NOT NULL, ranking INT NOT NULL, rule_names LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', formula_name VARCHAR(255) DEFAULT NULL, factor DOUBLE PRECISION DEFAULT NULL, against VARCHAR(255) NOT NULL, limit_amount INT DEFAULT NULL, limit_currency VARCHAR(255) DEFAULT NULL, limit_conversion JSON DEFAULT NULL COMMENT \'(DC2Type:json)\', INDEX IDX_C93C9F7B50A89B2C (call_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE project (id INT AUTO_INCREMENT NOT NULL, accounting_id INT DEFAULT NULL, owner_id INT NOT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(56) NOT NULL, subtitle VARCHAR(255) NOT NULL, deadline VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, status VARCHAR(255) NOT NULL, locales JSON NOT NULL COMMENT \'(DC2Type:json)\', migrated TINYINT(1) NOT NULL, migrated_id VARCHAR(255) DEFAULT NULL, date_created DATETIME NOT NULL, date_updated DATETIME NOT NULL, calendar_release DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', calendar_minimum DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', calendar_optimum DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', territory_country VARCHAR(255) DEFAULT NULL, territory_sub_lvl1 VARCHAR(255) DEFAULT NULL, territory_sub_lvl2 VARCHAR(255) DEFAULT NULL, video_src VARCHAR(255) DEFAULT NULL, video_thumbnail VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_2FB3D0EE989D9B62 (slug), UNIQUE INDEX UNIQ_2FB3D0EE3B7DD068 (accounting_id), INDEX IDX_2FB3D0EE7E3C61F9 (owner_id), INDEX IDX_2FB3D0EECAA6B324 (migrated_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE project_category (project_id INT NOT NULL, category_id VARCHAR(255) NOT NULL, INDEX IDX_3B02921A166D1F9C (project_id), INDEX IDX_3B02921A12469DE2 (category_id), PRIMARY KEY(project_id, category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE project_budget_item (id INT AUTO_INCREMENT NOT NULL, project_id INT NOT NULL, type VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, deadline VARCHAR(255) NOT NULL, migrated TINYINT(1) NOT NULL, migrated_id VARCHAR(255) DEFAULT NULL, locales JSON NOT NULL COMMENT \'(DC2Type:json)\', money_amount INT DEFAULT NULL, money_currency VARCHAR(255) DEFAULT NULL, money_conversion JSON DEFAULT NULL COMMENT \'(DC2Type:json)\', INDEX IDX_AD10D0B5166D1F9C (project_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE project_collaboration (id INT AUTO_INCREMENT NOT NULL, project_id INT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, is_fulfilled TINYINT(1) NOT NULL, locales JSON NOT NULL COMMENT \'(DC2Type:json)\', date_created DATETIME NOT NULL, date_updated DATETIME NOT NULL, INDEX IDX_EAB7425166D1F9C (project_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE project_reward (id INT AUTO_INCREMENT NOT NULL, project_id INT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, is_finite TINYINT(1) NOT NULL, units_total INT DEFAULT NULL, units_claimed INT DEFAULT NULL, units_available INT DEFAULT NULL, migrated TINYINT(1) NOT NULL, migrated_id VARCHAR(255) DEFAULT NULL, locales JSON NOT NULL COMMENT \'(DC2Type:json)\', date_created DATETIME NOT NULL, date_updated DATETIME NOT NULL, money_amount INT DEFAULT NULL, money_currency VARCHAR(255) DEFAULT NULL, money_conversion JSON DEFAULT NULL COMMENT \'(DC2Type:json)\', INDEX IDX_59759919166D1F9C (project_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE project_reward_claim (id INT AUTO_INCREMENT NOT NULL, owner_id INT NOT NULL, reward_id INT NOT NULL, charge_id INT NOT NULL, INDEX IDX_EA1261187E3C61F9 (owner_id), INDEX IDX_EA126118E466ACA1 (reward_id), INDEX IDX_EA12611855284914 (charge_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE project_support (id INT AUTO_INCREMENT NOT NULL, project_id INT NOT NULL, origin_id INT NOT NULL, anonymous TINYINT(1) NOT NULL, message LONGTEXT DEFAULT NULL, money_amount INT DEFAULT NULL, money_currency VARCHAR(255) DEFAULT NULL, money_conversion JSON DEFAULT NULL COMMENT \'(DC2Type:json)\', INDEX IDX_161AE7C0166D1F9C (project_id), INDEX IDX_161AE7C056A273CC (origin_id), UNIQUE INDEX project_origin_unique (project_id, origin_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE project_support_trxs (support_id INT NOT NULL, transaction_id INT NOT NULL, INDEX IDX_4E37F5A1315B405 (support_id), INDEX IDX_4E37F5A12FC0CB0F (transaction_id), PRIMARY KEY(support_id, transaction_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE project_update (id INT AUTO_INCREMENT NOT NULL, project_id INT NOT NULL, author_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, subtitle VARCHAR(255) NOT NULL, body LONGTEXT NOT NULL, date DATETIME NOT NULL, cover LONGTEXT DEFAULT NULL, locales JSON NOT NULL COMMENT \'(DC2Type:json)\', date_created DATETIME NOT NULL, date_updated DATETIME NOT NULL, INDEX IDX_8F81DE32166D1F9C (project_id), INDEX IDX_8F81DE32F675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE system_variable (name VARCHAR(255) NOT NULL, value LONGTEXT DEFAULT NULL, PRIMARY KEY(name)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tipjar (id INT AUTO_INCREMENT NOT NULL, accounting_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_59C15FD43B7DD068 (accounting_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, accounting_id INT DEFAULT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, handle VARCHAR(255) NOT NULL, roles JSON NOT NULL COMMENT \'(DC2Type:json)\', email_confirmed TINYINT(1) NOT NULL, active TINYINT(1) NOT NULL, type VARCHAR(255) NOT NULL, avatar LONGTEXT DEFAULT NULL, migrated TINYINT(1) NOT NULL, migrated_id VARCHAR(255) DEFAULT NULL, deduped TINYINT(1) NOT NULL, deduped_ids JSON NOT NULL COMMENT \'(DC2Type:json)\', date_created DATETIME NOT NULL, date_updated DATETIME NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), UNIQUE INDEX UNIQ_8D93D649918020D9 (handle), UNIQUE INDEX UNIQ_8D93D6493B7DD068 (accounting_id), INDEX IDX_8D93D649CAA6B324 (migrated_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_organization (user_id INT NOT NULL, tax_id VARCHAR(255) DEFAULT NULL, legal_name VARCHAR(255) DEFAULT NULL, business_name VARCHAR(255) DEFAULT NULL, PRIMARY KEY(user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_person (user_id INT NOT NULL, tax_id VARCHAR(255) DEFAULT NULL, first_name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) DEFAULT NULL, PRIMARY KEY(user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_token (id INT AUTO_INCREMENT NOT NULL, owner_id INT NOT NULL, token VARCHAR(68) NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_BDF55A637E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE wallet_financement (id INT AUTO_INCREMENT NOT NULL, origin_id INT NOT NULL, target_id INT NOT NULL, money_amount INT DEFAULT NULL, money_currency VARCHAR(255) DEFAULT NULL, money_conversion JSON DEFAULT NULL COMMENT \'(DC2Type:json)\', INDEX IDX_2C64A3F556A273CC (origin_id), INDEX IDX_2C64A3F5158E0B66 (target_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE wallet_statement (id INT AUTO_INCREMENT NOT NULL, transaction_id INT NOT NULL, direction VARCHAR(255) NOT NULL, balance_amount INT DEFAULT NULL, balance_currency VARCHAR(255) DEFAULT NULL, balance_conversion JSON DEFAULT NULL COMMENT \'(DC2Type:json)\', UNIQUE INDEX UNIQ_974741E72FC0CB0F (transaction_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE accounting_transaction ADD CONSTRAINT FK_E88BF0156A273CC FOREIGN KEY (origin_id) REFERENCES accounting (id)');
        $this->addSql('ALTER TABLE accounting_transaction ADD CONSTRAINT FK_E88BF01158E0B66 FOREIGN KEY (target_id) REFERENCES accounting (id)');
        $this->addSql('ALTER TABLE checkout ADD CONSTRAINT FK_AF382D4E56A273CC FOREIGN KEY (origin_id) REFERENCES accounting (id)');
        $this->addSql('ALTER TABLE checkout_charge ADD CONSTRAINT FK_8707A16F146D8724 FOREIGN KEY (checkout_id) REFERENCES checkout (id)');
        $this->addSql('ALTER TABLE checkout_charge ADD CONSTRAINT FK_8707A16F158E0B66 FOREIGN KEY (target_id) REFERENCES accounting (id)');
        $this->addSql('ALTER TABLE checkout_charge_trxs ADD CONSTRAINT FK_7E9B717F55284914 FOREIGN KEY (charge_id) REFERENCES checkout_charge (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE checkout_charge_trxs ADD CONSTRAINT FK_7E9B717F2FC0CB0F FOREIGN KEY (transaction_id) REFERENCES accounting_transaction (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE checkout_tracking ADD CONSTRAINT FK_8159C060146D8724 FOREIGN KEY (checkout_id) REFERENCES checkout (id)');
        $this->addSql('ALTER TABLE match_call ADD CONSTRAINT FK_ADE19E4C3B7DD068 FOREIGN KEY (accounting_id) REFERENCES accounting (id)');
        $this->addSql('ALTER TABLE match_call_user ADD CONSTRAINT FK_A3A3FDBE270EC443 FOREIGN KEY (match_call_id) REFERENCES match_call (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE match_call_user ADD CONSTRAINT FK_A3A3FDBEA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE match_call_submission ADD CONSTRAINT FK_A77BFE0C50A89B2C FOREIGN KEY (call_id) REFERENCES match_call (id)');
        $this->addSql('ALTER TABLE match_call_submission ADD CONSTRAINT FK_A77BFE0C166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');
        $this->addSql('ALTER TABLE match_strategy ADD CONSTRAINT FK_C93C9F7B50A89B2C FOREIGN KEY (call_id) REFERENCES match_call (id)');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EE3B7DD068 FOREIGN KEY (accounting_id) REFERENCES accounting (id)');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EE7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE project_category ADD CONSTRAINT FK_3B02921A166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE project_category ADD CONSTRAINT FK_3B02921A12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE project_budget_item ADD CONSTRAINT FK_AD10D0B5166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');
        $this->addSql('ALTER TABLE project_collaboration ADD CONSTRAINT FK_EAB7425166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');
        $this->addSql('ALTER TABLE project_reward ADD CONSTRAINT FK_59759919166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');
        $this->addSql('ALTER TABLE project_reward_claim ADD CONSTRAINT FK_EA1261187E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE project_reward_claim ADD CONSTRAINT FK_EA126118E466ACA1 FOREIGN KEY (reward_id) REFERENCES project_reward (id)');
        $this->addSql('ALTER TABLE project_reward_claim ADD CONSTRAINT FK_EA12611855284914 FOREIGN KEY (charge_id) REFERENCES checkout_charge (id)');
        $this->addSql('ALTER TABLE project_support ADD CONSTRAINT FK_161AE7C0166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');
        $this->addSql('ALTER TABLE project_support ADD CONSTRAINT FK_161AE7C056A273CC FOREIGN KEY (origin_id) REFERENCES accounting (id)');
        $this->addSql('ALTER TABLE project_support_trxs ADD CONSTRAINT FK_4E37F5A1315B405 FOREIGN KEY (support_id) REFERENCES project_support (id)');
        $this->addSql('ALTER TABLE project_support_trxs ADD CONSTRAINT FK_4E37F5A12FC0CB0F FOREIGN KEY (transaction_id) REFERENCES accounting_transaction (id)');
        $this->addSql('ALTER TABLE project_update ADD CONSTRAINT FK_8F81DE32166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');
        $this->addSql('ALTER TABLE project_update ADD CONSTRAINT FK_8F81DE32F675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE tipjar ADD CONSTRAINT FK_59C15FD43B7DD068 FOREIGN KEY (accounting_id) REFERENCES accounting (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6493B7DD068 FOREIGN KEY (accounting_id) REFERENCES accounting (id)');
        $this->addSql('ALTER TABLE user_organization ADD CONSTRAINT FK_41221F7EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_person ADD CONSTRAINT FK_518ECA4BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_token ADD CONSTRAINT FK_BDF55A637E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE wallet_financement ADD CONSTRAINT FK_2C64A3F556A273CC FOREIGN KEY (origin_id) REFERENCES wallet_statement (id)');
        $this->addSql('ALTER TABLE wallet_financement ADD CONSTRAINT FK_2C64A3F5158E0B66 FOREIGN KEY (target_id) REFERENCES wallet_statement (id)');
        $this->addSql('ALTER TABLE wallet_statement ADD CONSTRAINT FK_974741E72FC0CB0F FOREIGN KEY (transaction_id) REFERENCES accounting_transaction (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE accounting_transaction DROP FOREIGN KEY FK_E88BF0156A273CC');
        $this->addSql('ALTER TABLE accounting_transaction DROP FOREIGN KEY FK_E88BF01158E0B66');
        $this->addSql('ALTER TABLE checkout DROP FOREIGN KEY FK_AF382D4E56A273CC');
        $this->addSql('ALTER TABLE checkout_charge DROP FOREIGN KEY FK_8707A16F146D8724');
        $this->addSql('ALTER TABLE checkout_charge DROP FOREIGN KEY FK_8707A16F158E0B66');
        $this->addSql('ALTER TABLE checkout_charge_trxs DROP FOREIGN KEY FK_7E9B717F55284914');
        $this->addSql('ALTER TABLE checkout_charge_trxs DROP FOREIGN KEY FK_7E9B717F2FC0CB0F');
        $this->addSql('ALTER TABLE checkout_tracking DROP FOREIGN KEY FK_8159C060146D8724');
        $this->addSql('ALTER TABLE match_call DROP FOREIGN KEY FK_ADE19E4C3B7DD068');
        $this->addSql('ALTER TABLE match_call_user DROP FOREIGN KEY FK_A3A3FDBE270EC443');
        $this->addSql('ALTER TABLE match_call_user DROP FOREIGN KEY FK_A3A3FDBEA76ED395');
        $this->addSql('ALTER TABLE match_call_submission DROP FOREIGN KEY FK_A77BFE0C50A89B2C');
        $this->addSql('ALTER TABLE match_call_submission DROP FOREIGN KEY FK_A77BFE0C166D1F9C');
        $this->addSql('ALTER TABLE match_strategy DROP FOREIGN KEY FK_C93C9F7B50A89B2C');
        $this->addSql('ALTER TABLE project DROP FOREIGN KEY FK_2FB3D0EE3B7DD068');
        $this->addSql('ALTER TABLE project DROP FOREIGN KEY FK_2FB3D0EE7E3C61F9');
        $this->addSql('ALTER TABLE project_category DROP FOREIGN KEY FK_3B02921A166D1F9C');
        $this->addSql('ALTER TABLE project_category DROP FOREIGN KEY FK_3B02921A12469DE2');
        $this->addSql('ALTER TABLE project_budget_item DROP FOREIGN KEY FK_AD10D0B5166D1F9C');
        $this->addSql('ALTER TABLE project_collaboration DROP FOREIGN KEY FK_EAB7425166D1F9C');
        $this->addSql('ALTER TABLE project_reward DROP FOREIGN KEY FK_59759919166D1F9C');
        $this->addSql('ALTER TABLE project_reward_claim DROP FOREIGN KEY FK_EA1261187E3C61F9');
        $this->addSql('ALTER TABLE project_reward_claim DROP FOREIGN KEY FK_EA126118E466ACA1');
        $this->addSql('ALTER TABLE project_reward_claim DROP FOREIGN KEY FK_EA12611855284914');
        $this->addSql('ALTER TABLE project_support DROP FOREIGN KEY FK_161AE7C0166D1F9C');
        $this->addSql('ALTER TABLE project_support DROP FOREIGN KEY FK_161AE7C056A273CC');
        $this->addSql('ALTER TABLE project_support_trxs DROP FOREIGN KEY FK_4E37F5A1315B405');
        $this->addSql('ALTER TABLE project_support_trxs DROP FOREIGN KEY FK_4E37F5A12FC0CB0F');
        $this->addSql('ALTER TABLE project_update DROP FOREIGN KEY FK_8F81DE32166D1F9C');
        $this->addSql('ALTER TABLE project_update DROP FOREIGN KEY FK_8F81DE32F675F31B');
        $this->addSql('ALTER TABLE tipjar DROP FOREIGN KEY FK_59C15FD43B7DD068');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6493B7DD068');
        $this->addSql('ALTER TABLE user_organization DROP FOREIGN KEY FK_41221F7EA76ED395');
        $this->addSql('ALTER TABLE user_person DROP FOREIGN KEY FK_518ECA4BA76ED395');
        $this->addSql('ALTER TABLE user_token DROP FOREIGN KEY FK_BDF55A637E3C61F9');
        $this->addSql('ALTER TABLE wallet_financement DROP FOREIGN KEY FK_2C64A3F556A273CC');
        $this->addSql('ALTER TABLE wallet_financement DROP FOREIGN KEY FK_2C64A3F5158E0B66');
        $this->addSql('ALTER TABLE wallet_statement DROP FOREIGN KEY FK_974741E72FC0CB0F');
        $this->addSql('DROP TABLE accounting');
        $this->addSql('DROP TABLE accounting_transaction');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE checkout');
        $this->addSql('DROP TABLE checkout_charge');
        $this->addSql('DROP TABLE checkout_charge_trxs');
        $this->addSql('DROP TABLE checkout_tracking');
        $this->addSql('DROP TABLE ext_log_entries');
        $this->addSql('DROP TABLE ext_translations');
        $this->addSql('DROP TABLE match_call');
        $this->addSql('DROP TABLE match_call_user');
        $this->addSql('DROP TABLE match_call_submission');
        $this->addSql('DROP TABLE match_strategy');
        $this->addSql('DROP TABLE project');
        $this->addSql('DROP TABLE project_category');
        $this->addSql('DROP TABLE project_budget_item');
        $this->addSql('DROP TABLE project_collaboration');
        $this->addSql('DROP TABLE project_reward');
        $this->addSql('DROP TABLE project_reward_claim');
        $this->addSql('DROP TABLE project_support');
        $this->addSql('DROP TABLE project_support_trxs');
        $this->addSql('DROP TABLE project_update');
        $this->addSql('DROP TABLE system_variable');
        $this->addSql('DROP TABLE tipjar');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_organization');
        $this->addSql('DROP TABLE user_person');
        $this->addSql('DROP TABLE user_token');
        $this->addSql('DROP TABLE wallet_financement');
        $this->addSql('DROP TABLE wallet_statement');
    }
}
