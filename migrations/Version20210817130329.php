<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210817130329 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE document (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', document_name VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, uploaded_by VARCHAR(255) NOT NULL, uploaded_at DATETIME NOT NULL, filename VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE document_case_entity (document_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', case_entity_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_256F34ABC33F7837 (document_id), INDEX IDX_256F34ABAF060DA6 (case_entity_id), PRIMARY KEY(document_id, case_entity_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE document_case_entity ADD CONSTRAINT FK_256F34ABC33F7837 FOREIGN KEY (document_id) REFERENCES document (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE document_case_entity ADD CONSTRAINT FK_256F34ABAF060DA6 FOREIGN KEY (case_entity_id) REFERENCES case_entity (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE document_case_entity DROP FOREIGN KEY FK_256F34ABC33F7837');
        $this->addSql('DROP TABLE document');
        $this->addSql('DROP TABLE document_case_entity');
    }
}
