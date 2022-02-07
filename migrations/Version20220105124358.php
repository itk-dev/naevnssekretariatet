<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220105124358 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE mail_template (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', created_by_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', updated_by_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, template_filename VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_4AB7DECBB03A8386 (created_by_id), INDEX IDX_4AB7DECB896DBBDE (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mail_template_macro (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', created_by_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', updated_by_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) NOT NULL, macro VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, template_types LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_94F57BA5B03A8386 (created_by_id), INDEX IDX_94F57BA5896DBBDE (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE mail_template ADD CONSTRAINT FK_4AB7DECBB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE mail_template ADD CONSTRAINT FK_4AB7DECB896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE mail_template_macro ADD CONSTRAINT FK_94F57BA5B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE mail_template_macro ADD CONSTRAINT FK_94F57BA5896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE mail_template');
        $this->addSql('DROP TABLE mail_template_macro');
    }
}
