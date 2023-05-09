<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230504091710 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE hearing_briefing (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', template_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', title VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, custom_data LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', INDEX IDX_D35055A55DA0FB8 (template_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE hearing_briefing_document (hearing_briefing_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', document_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_F22372211555A28D (hearing_briefing_id), INDEX IDX_F2237221C33F7837 (document_id), PRIMARY KEY(hearing_briefing_id, document_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE hearing_briefing_recipient (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', hearing_briefing_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', recipient_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', document_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_9D2175E61555A28D (hearing_briefing_id), INDEX IDX_9D2175E6E92F8F78 (recipient_id), INDEX IDX_9D2175E6C33F7837 (document_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE hearing_briefing ADD CONSTRAINT FK_D35055A55DA0FB8 FOREIGN KEY (template_id) REFERENCES mail_template (id)');
        $this->addSql('ALTER TABLE hearing_briefing_document ADD CONSTRAINT FK_F22372211555A28D FOREIGN KEY (hearing_briefing_id) REFERENCES hearing_briefing (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE hearing_briefing_document ADD CONSTRAINT FK_F2237221C33F7837 FOREIGN KEY (document_id) REFERENCES document (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE hearing_briefing_recipient ADD CONSTRAINT FK_9D2175E61555A28D FOREIGN KEY (hearing_briefing_id) REFERENCES hearing_briefing (id)');
        $this->addSql('ALTER TABLE hearing_briefing_recipient ADD CONSTRAINT FK_9D2175E6E92F8F78 FOREIGN KEY (recipient_id) REFERENCES party (id)');
        $this->addSql('ALTER TABLE hearing_briefing_recipient ADD CONSTRAINT FK_9D2175E6C33F7837 FOREIGN KEY (document_id) REFERENCES document (id)');
        $this->addSql('ALTER TABLE hearing_post_request ADD briefing_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', ADD should_send_briefing TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE hearing_post_request ADD CONSTRAINT FK_A12A6D0027266F69 FOREIGN KEY (briefing_id) REFERENCES hearing_briefing (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A12A6D0027266F69 ON hearing_post_request (briefing_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE hearing_briefing_document DROP FOREIGN KEY FK_F22372211555A28D');
        $this->addSql('ALTER TABLE hearing_briefing_recipient DROP FOREIGN KEY FK_9D2175E61555A28D');
        $this->addSql('ALTER TABLE hearing_post_request DROP FOREIGN KEY FK_A12A6D0027266F69');
        $this->addSql('DROP TABLE hearing_briefing');
        $this->addSql('DROP TABLE hearing_briefing_document');
        $this->addSql('DROP TABLE hearing_briefing_recipient');
        $this->addSql('DROP INDEX UNIQ_A12A6D0027266F69 ON hearing_post_request');
        $this->addSql('ALTER TABLE hearing_post_request DROP briefing_id, DROP should_send_briefing');
    }
}
