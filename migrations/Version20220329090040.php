<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220329090040 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE party ADD identification_p_number VARCHAR(10) DEFAULT NULL, ADD identification_validated_at DATETIME DEFAULT NULL, CHANGE identifier_type identification_type VARCHAR(32) NOT NULL, CHANGE identifier identification_identifier VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE party ADD identifier VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, ADD identifier_type VARCHAR(32) NOT NULL COLLATE `utf8mb4_unicode_ci`, DROP identification_type, DROP identification_identifier, DROP identification_p_number, DROP identification_validated_at, CHANGE phone_number phone_number VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE name name VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE address_street address_street VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE address_number address_number VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE address_floor address_floor VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE address_side address_side VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE address_city address_city VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE address_bbr_data address_bbr_data LONGTEXT DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:json)\', CHANGE address_extra_information address_extra_information VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
