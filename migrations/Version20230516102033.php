<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230516102033 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE hearing_briefing_recipient_document (hearing_briefing_recipient_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', document_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_93079F36D50FF452 (hearing_briefing_recipient_id), INDEX IDX_93079F36C33F7837 (document_id), PRIMARY KEY(hearing_briefing_recipient_id, document_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE hearing_briefing_recipient_document ADD CONSTRAINT FK_93079F36D50FF452 FOREIGN KEY (hearing_briefing_recipient_id) REFERENCES hearing_briefing_recipient (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE hearing_briefing_recipient_document ADD CONSTRAINT FK_93079F36C33F7837 FOREIGN KEY (document_id) REFERENCES document (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE hearing_briefing_document');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE hearing_briefing_document (hearing_briefing_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', document_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_F2237221C33F7837 (document_id), INDEX IDX_F22372211555A28D (hearing_briefing_id), PRIMARY KEY(hearing_briefing_id, document_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE hearing_briefing_document ADD CONSTRAINT FK_F22372211555A28D FOREIGN KEY (hearing_briefing_id) REFERENCES hearing_briefing (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE hearing_briefing_document ADD CONSTRAINT FK_F2237221C33F7837 FOREIGN KEY (document_id) REFERENCES document (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE hearing_briefing_recipient_document');
    }
}
