<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220215102643 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE party ADD address_number VARCHAR(255) NOT NULL, ADD address_floor VARCHAR(255) DEFAULT NULL, ADD address_side VARCHAR(255) DEFAULT NULL, ADD address_postal_code INT NOT NULL, ADD address_city VARCHAR(255) NOT NULL, ADD address_validated_at DATETIME DEFAULT NULL, ADD address_bbr_data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', CHANGE address address_street VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE party ADD address VARCHAR(255) NOT NULL, DROP address_street, DROP address_number, DROP address_floor, DROP address_side, DROP address_postal_code, DROP address_city, DROP address_validated_at, DROP address_bbr_data');
    }
}
