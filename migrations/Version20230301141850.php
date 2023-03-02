<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230301141850 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE case_event_party_relation');
        $this->addSql('ALTER TABLE case_event ADD senders LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', ADD recipients LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE case_event_party_relation (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', case_event_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', party_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', type VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_1A485257213C1059 (party_id), INDEX IDX_1A485257E68A92F6 (case_event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE case_event_party_relation ADD CONSTRAINT FK_1A485257213C1059 FOREIGN KEY (party_id) REFERENCES party (id)');
        $this->addSql('ALTER TABLE case_event_party_relation ADD CONSTRAINT FK_1A485257E68A92F6 FOREIGN KEY (case_event_id) REFERENCES case_event (id)');
        $this->addSql('ALTER TABLE case_event DROP senders, DROP recipients');
    }
}
