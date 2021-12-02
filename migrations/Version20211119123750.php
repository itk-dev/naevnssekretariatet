<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211119123750 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE case_entity ADD presentation_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', ADD decision_proposal_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE case_entity ADD CONSTRAINT FK_A7C603C1AB627E8B FOREIGN KEY (presentation_id) REFERENCES case_presentation (id)');
        $this->addSql('ALTER TABLE case_entity ADD CONSTRAINT FK_A7C603C1198BBD31 FOREIGN KEY (decision_proposal_id) REFERENCES case_decision_proposal (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A7C603C1AB627E8B ON case_entity (presentation_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A7C603C1198BBD31 ON case_entity (decision_proposal_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE case_entity DROP FOREIGN KEY FK_A7C603C1AB627E8B');
        $this->addSql('ALTER TABLE case_entity DROP FOREIGN KEY FK_A7C603C1198BBD31');
        $this->addSql('DROP INDEX UNIQ_A7C603C1AB627E8B ON case_entity');
        $this->addSql('DROP INDEX UNIQ_A7C603C1198BBD31 ON case_entity');
        $this->addSql('ALTER TABLE case_entity DROP presentation_id, DROP decision_proposal_id');
    }
}
