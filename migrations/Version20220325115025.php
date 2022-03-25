<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220325115025 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE board CHANGE complainant_types party_types LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE case_entity ADD bringer VARCHAR(255) NOT NULL, ADD sorting_party VARCHAR(255) DEFAULT NULL, ADD bringer_address_street VARCHAR(255) NOT NULL, ADD bringer_address_number VARCHAR(255) NOT NULL, ADD bringer_address_floor VARCHAR(255) DEFAULT NULL, ADD bringer_address_side VARCHAR(255) DEFAULT NULL, ADD bringer_address_city VARCHAR(255) NOT NULL, ADD bringer_address_validated_at DATETIME DEFAULT NULL, ADD bringer_address_extra_information VARCHAR(255) DEFAULT NULL, ADD bringer_identification_identifier VARCHAR(255) NOT NULL, ADD bringer_identification_validated_at DATETIME DEFAULT NULL, DROP complainant, DROP complainant_identification_identifier, DROP complainant_address_street, DROP complainant_address_number, DROP complainant_address_floor, DROP complainant_address_side, DROP complainant_address_city, DROP complainant_address_validated_at, DROP sorting_complainant, DROP complainant_identification_validated_at, DROP complainant_address_extra_information, CHANGE complainant_address_postal_code bringer_address_postal_code INT NOT NULL, CHANGE complainant_address_bbr_data bringer_address_bbr_data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', CHANGE complainant_identification_type bringer_identification_type VARCHAR(32) NOT NULL');
        $this->addSql('ALTER TABLE fence_review_case CHANGE complainant_claim bringer_claim LONGTEXT NOT NULL, CHANGE complainant_cadastral_number bringer_cadastral_number VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE hearing CHANGE complainant_has_no_more_to_add party_has_no_more_to_add TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE rent_board_case CHANGE complainant_phone bringer_phone INT NOT NULL');
        $this->addSql('ALTER TABLE resident_complaint_board_case CHANGE complainant_phone bringer_phone INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE board ADD complainant_types LONGTEXT NOT NULL COLLATE `utf8mb4_unicode_ci`, DROP party_types, CHANGE name name VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE case_form_type case_form_type VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE statuses statuses LONGTEXT NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE counterparty_types counterparty_types LONGTEXT NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE case_entity ADD complainant VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, ADD complainant_identification_identifier VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, ADD complainant_address_street VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, ADD complainant_address_number VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, ADD complainant_address_floor VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD complainant_address_side VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD complainant_address_city VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, ADD complainant_address_bbr_data LONGTEXT DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:json)\', ADD complainant_address_validated_at DATETIME DEFAULT NULL, ADD sorting_complainant VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD complainant_identification_type VARCHAR(32) NOT NULL COLLATE `utf8mb4_unicode_ci`, ADD complainant_identification_validated_at DATETIME DEFAULT NULL, ADD complainant_address_extra_information VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, DROP bringer, DROP sorting_party, DROP bringer_address_street, DROP bringer_address_number, DROP bringer_address_floor, DROP bringer_address_side, DROP bringer_address_city, DROP bringer_address_validated_at, DROP bringer_address_bbr_data, DROP bringer_address_extra_information, DROP bringer_identification_type, DROP bringer_identification_identifier, DROP bringer_identification_validated_at, CHANGE bringer_address_postal_code complainant_address_postal_code INT NOT NULL');
        $this->addSql('ALTER TABLE fence_review_case ADD complainant_claim LONGTEXT NOT NULL COLLATE `utf8mb4_unicode_ci`, ADD complainant_cadastral_number VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, DROP bringer_claim, DROP bringer_cadastral_number');
        $this->addSql('ALTER TABLE hearing CHANGE party_has_no_more_to_add complainant_has_no_more_to_add TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE rent_board_case CHANGE bringer_phone complainant_phone INT NOT NULL');
        $this->addSql('ALTER TABLE resident_complaint_board_case CHANGE bringer_phone complainant_phone INT NOT NULL');
    }
}
