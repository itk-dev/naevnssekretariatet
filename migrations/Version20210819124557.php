<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210819124557 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE case_documents (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', case_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', document_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_8C930273CF10D4F5 (case_id), INDEX IDX_8C930273C33F7837 (document_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE case_documents ADD CONSTRAINT FK_8C930273CF10D4F5 FOREIGN KEY (case_id) REFERENCES case_entity (id)');
        $this->addSql('ALTER TABLE case_documents ADD CONSTRAINT FK_8C930273C33F7837 FOREIGN KEY (document_id) REFERENCES document (id)');
        $this->addSql('DROP TABLE case_document_relation');
        $this->addSql('DROP TABLE document_case_entity');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE case_document_relation (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', case_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', document_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_5CE888DACF10D4F5 (case_id), INDEX IDX_5CE888DAC33F7837 (document_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE document_case_entity (document_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', case_entity_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_256F34ABC33F7837 (document_id), INDEX IDX_256F34ABAF060DA6 (case_entity_id), PRIMARY KEY(document_id, case_entity_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE case_document_relation ADD CONSTRAINT FK_5CE888DAC33F7837 FOREIGN KEY (document_id) REFERENCES document (id)');
        $this->addSql('ALTER TABLE case_document_relation ADD CONSTRAINT FK_5CE888DACF10D4F5 FOREIGN KEY (case_id) REFERENCES case_entity (id)');
        $this->addSql('ALTER TABLE document_case_entity ADD CONSTRAINT FK_256F34ABAF060DA6 FOREIGN KEY (case_entity_id) REFERENCES case_entity (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE document_case_entity ADD CONSTRAINT FK_256F34ABC33F7837 FOREIGN KEY (document_id) REFERENCES document (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE case_documents');
    }
}
