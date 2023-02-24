<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230220142106 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE case_event (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', case_entity_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', digital_post_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', created_by_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', category VARCHAR(255) NOT NULL, subject VARCHAR(255) NOT NULL, note_content LONGTEXT DEFAULT NULL, received_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_6947F04BAF060DA6 (case_entity_id), UNIQUE INDEX UNIQ_6947F04B6B0BD826 (digital_post_id), INDEX IDX_6947F04BB03A8386 (created_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE case_event_document (case_event_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', document_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_FB5253E3E68A92F6 (case_event_id), INDEX IDX_FB5253E3C33F7837 (document_id), PRIMARY KEY(case_event_id, document_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE case_event_party_relation (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', case_event_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', party_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', type VARCHAR(255) NOT NULL, INDEX IDX_1A485257E68A92F6 (case_event_id), INDEX IDX_1A485257213C1059 (party_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE case_event ADD CONSTRAINT FK_6947F04BAF060DA6 FOREIGN KEY (case_entity_id) REFERENCES case_entity (id)');
        $this->addSql('ALTER TABLE case_event ADD CONSTRAINT FK_6947F04B6B0BD826 FOREIGN KEY (digital_post_id) REFERENCES digital_post (id)');
        $this->addSql('ALTER TABLE case_event ADD CONSTRAINT FK_6947F04BB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE case_event_document ADD CONSTRAINT FK_FB5253E3E68A92F6 FOREIGN KEY (case_event_id) REFERENCES case_event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE case_event_document ADD CONSTRAINT FK_FB5253E3C33F7837 FOREIGN KEY (document_id) REFERENCES document (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE case_event_party_relation ADD CONSTRAINT FK_1A485257E68A92F6 FOREIGN KEY (case_event_id) REFERENCES case_event (id)');
        $this->addSql('ALTER TABLE case_event_party_relation ADD CONSTRAINT FK_1A485257213C1059 FOREIGN KEY (party_id) REFERENCES party (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE case_event_document DROP FOREIGN KEY FK_FB5253E3E68A92F6');
        $this->addSql('ALTER TABLE case_event_party_relation DROP FOREIGN KEY FK_1A485257E68A92F6');
        $this->addSql('DROP TABLE case_event');
        $this->addSql('DROP TABLE case_event_document');
        $this->addSql('DROP TABLE case_event_party_relation');
    }
}
