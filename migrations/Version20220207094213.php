<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220207094213 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE document_digital_post (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', serial_number VARCHAR(255) NOT NULL, recipient_cpr VARCHAR(10) NOT NULL, recipient_name VARCHAR(255) NOT NULL, status VARCHAR(32) DEFAULT NULL, recipient_address_street VARCHAR(255) NOT NULL, recipient_address_number VARCHAR(255) NOT NULL, recipient_address_floor VARCHAR(255) DEFAULT NULL, recipient_address_side VARCHAR(255) DEFAULT NULL, recipient_address_postal_code INT NOT NULL, recipient_address_city VARCHAR(255) NOT NULL, recipient_address_validated_at DATETIME DEFAULT NULL, recipient_address_bbr_data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', UNIQUE INDEX UNIQ_AE66CCB3D948EE2 (serial_number), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE document_digital_post ADD CONSTRAINT FK_AE66CCB3BF396750 FOREIGN KEY (id) REFERENCES document (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE document ADD discr VARCHAR(255) NOT NULL');
        $this->addSql('UPDATE document SET discr = \'document\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE document_digital_post');
        $this->addSql('ALTER TABLE document DROP discr');
    }
}
