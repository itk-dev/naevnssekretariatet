<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220216151318 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE hearing_post_attachment (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', hearing_post_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', document_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', position INT NOT NULL, INDEX IDX_568878B3CF583A5C (hearing_post_id), INDEX IDX_568878B3C33F7837 (document_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE hearing_post_attachment ADD CONSTRAINT FK_568878B3CF583A5C FOREIGN KEY (hearing_post_id) REFERENCES hearing_post (id)');
        $this->addSql('ALTER TABLE hearing_post_attachment ADD CONSTRAINT FK_568878B3C33F7837 FOREIGN KEY (document_id) REFERENCES document (id)');
        $this->addSql('ALTER TABLE hearing_post DROP content');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE hearing_post_attachment');
        $this->addSql('ALTER TABLE hearing_post ADD content LONGTEXT NOT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
