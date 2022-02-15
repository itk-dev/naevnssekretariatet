<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220210094822 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE digital_post (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', document_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', entity_type VARCHAR(255) DEFAULT NULL, entity_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', status VARCHAR(32) DEFAULT NULL, data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', sent_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_383EDC2C33F7837 (document_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE digital_post_document (digital_post_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', document_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_472756A96B0BD826 (digital_post_id), INDEX IDX_472756A9C33F7837 (document_id), PRIMARY KEY(digital_post_id, document_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE recipient (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', digital_post_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) NOT NULL, identifier VARCHAR(255) NOT NULL, identifier_type VARCHAR(32) NOT NULL, address_street VARCHAR(255) NOT NULL, address_number VARCHAR(255) NOT NULL, address_floor VARCHAR(255) DEFAULT NULL, address_side VARCHAR(255) DEFAULT NULL, address_postal_code INT NOT NULL, address_city VARCHAR(255) NOT NULL, address_validated_at DATETIME DEFAULT NULL, address_bbr_data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', INDEX IDX_6804FB496B0BD826 (digital_post_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE digital_post ADD CONSTRAINT FK_383EDC2C33F7837 FOREIGN KEY (document_id) REFERENCES document (id)');
        $this->addSql('ALTER TABLE digital_post_document ADD CONSTRAINT FK_472756A96B0BD826 FOREIGN KEY (digital_post_id) REFERENCES digital_post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE digital_post_document ADD CONSTRAINT FK_472756A9C33F7837 FOREIGN KEY (document_id) REFERENCES document (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE recipient ADD CONSTRAINT FK_6804FB496B0BD826 FOREIGN KEY (digital_post_id) REFERENCES digital_post (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE digital_post_document DROP FOREIGN KEY FK_472756A96B0BD826');
        $this->addSql('ALTER TABLE recipient DROP FOREIGN KEY FK_6804FB496B0BD826');
        $this->addSql('DROP TABLE digital_post');
        $this->addSql('DROP TABLE digital_post_document');
        $this->addSql('DROP TABLE recipient');
    }
}
