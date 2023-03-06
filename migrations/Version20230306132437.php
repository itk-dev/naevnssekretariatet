<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230306132437 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE case_event_case_entity (case_event_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', case_entity_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_BDEFEE8BE68A92F6 (case_event_id), INDEX IDX_BDEFEE8BAF060DA6 (case_entity_id), PRIMARY KEY(case_event_id, case_entity_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE case_event_case_entity ADD CONSTRAINT FK_BDEFEE8BE68A92F6 FOREIGN KEY (case_event_id) REFERENCES case_event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE case_event_case_entity ADD CONSTRAINT FK_BDEFEE8BAF060DA6 FOREIGN KEY (case_entity_id) REFERENCES case_entity (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE case_event DROP FOREIGN KEY FK_6947F04BAF060DA6');
        $this->addSql('DROP INDEX IDX_6947F04BAF060DA6 ON case_event');
        $this->addSql('ALTER TABLE case_event DROP case_entity_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE case_event_case_entity');
        $this->addSql('ALTER TABLE case_event ADD case_entity_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE case_event ADD CONSTRAINT FK_6947F04BAF060DA6 FOREIGN KEY (case_entity_id) REFERENCES case_entity (id)');
        $this->addSql('CREATE INDEX IDX_6947F04BAF060DA6 ON case_event (case_entity_id)');
    }
}
