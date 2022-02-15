<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220215151906 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE digital_post_attachment (id INT AUTO_INCREMENT NOT NULL, digital_post_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', document_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', position INT NOT NULL, INDEX IDX_F09A01DA6B0BD826 (digital_post_id), INDEX IDX_F09A01DAC33F7837 (document_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE digital_post_attachment ADD CONSTRAINT FK_F09A01DA6B0BD826 FOREIGN KEY (digital_post_id) REFERENCES digital_post (id)');
        $this->addSql('ALTER TABLE digital_post_attachment ADD CONSTRAINT FK_F09A01DAC33F7837 FOREIGN KEY (document_id) REFERENCES document (id)');
        $this->addSql('DROP TABLE digital_post_document');
        $this->addSql('ALTER TABLE digital_post ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL');
        $this->addSql('CREATE INDEX entity_idx ON digital_post (entity_type, entity_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE digital_post_document (digital_post_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', document_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_472756A96B0BD826 (digital_post_id), INDEX IDX_472756A9C33F7837 (document_id), PRIMARY KEY(digital_post_id, document_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE digital_post_document ADD CONSTRAINT FK_472756A9C33F7837 FOREIGN KEY (document_id) REFERENCES document (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE digital_post_document ADD CONSTRAINT FK_472756A96B0BD826 FOREIGN KEY (digital_post_id) REFERENCES digital_post (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE digital_post_attachment');
        $this->addSql('DROP INDEX entity_idx ON digital_post');
        $this->addSql('ALTER TABLE digital_post DROP created_at, DROP updated_at, CHANGE entity_type entity_type VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE status status VARCHAR(32) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE data data LONGTEXT DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:json)\'');
    }
}
