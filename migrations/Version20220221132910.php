<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220221132910 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE decision (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', document_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', case_entity_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_84ACBE48C33F7837 (document_id), INDEX IDX_84ACBE48AF060DA6 (case_entity_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE decision_party (decision_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', party_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_AED089E9BDEE7539 (decision_id), INDEX IDX_AED089E9213C1059 (party_id), PRIMARY KEY(decision_id, party_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE decision_attachment (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', decision_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', document_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', position INT NOT NULL, INDEX IDX_3341903BBDEE7539 (decision_id), INDEX IDX_3341903BC33F7837 (document_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE decision ADD CONSTRAINT FK_84ACBE48C33F7837 FOREIGN KEY (document_id) REFERENCES document (id)');
        $this->addSql('ALTER TABLE decision ADD CONSTRAINT FK_84ACBE48AF060DA6 FOREIGN KEY (case_entity_id) REFERENCES case_entity (id)');
        $this->addSql('ALTER TABLE decision_party ADD CONSTRAINT FK_AED089E9BDEE7539 FOREIGN KEY (decision_id) REFERENCES decision (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE decision_party ADD CONSTRAINT FK_AED089E9213C1059 FOREIGN KEY (party_id) REFERENCES party (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE decision_attachment ADD CONSTRAINT FK_3341903BBDEE7539 FOREIGN KEY (decision_id) REFERENCES decision (id)');
        $this->addSql('ALTER TABLE decision_attachment ADD CONSTRAINT FK_3341903BC33F7837 FOREIGN KEY (document_id) REFERENCES document (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE decision_party DROP FOREIGN KEY FK_AED089E9BDEE7539');
        $this->addSql('ALTER TABLE decision_attachment DROP FOREIGN KEY FK_3341903BBDEE7539');
        $this->addSql('DROP TABLE decision');
        $this->addSql('DROP TABLE decision_party');
        $this->addSql('DROP TABLE decision_attachment');
    }
}
