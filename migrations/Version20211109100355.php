<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211109100355 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE case_entity ADD assigned_to_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', ADD complainant VARCHAR(255) NOT NULL, ADD complainant_street_name_and_number VARCHAR(255) NOT NULL, ADD complainant_zip VARCHAR(255) NOT NULL, ADD complainant_city VARCHAR(255) NOT NULL, ADD complainant_cpr VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE case_entity ADD CONSTRAINT FK_A7C603C1F4BD7827 FOREIGN KEY (assigned_to_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_A7C603C1F4BD7827 ON case_entity (assigned_to_id)');
        $this->addSql('ALTER TABLE resident_complaint_board_case ADD lease_size INT DEFAULT NULL, ADD has_vacated TINYINT(1) NOT NULL, ADD lease_street_name_and_number VARCHAR(255) NOT NULL, ADD lease_zip VARCHAR(255) NOT NULL, ADD lease_city VARCHAR(255) NOT NULL, ADD lease_started DATETIME DEFAULT NULL, ADD lease_agreed_rent INT DEFAULT NULL, ADD lease_interior_maintenance VARCHAR(255) DEFAULT NULL, ADD lease_regulated_rent INT DEFAULT NULL, ADD lease_rent_at_collection_time INT DEFAULT NULL, ADD lease_security_deposit INT DEFAULT NULL, ADD previous_cases_at_lease LONGTEXT DEFAULT NULL, ADD prepaid_rent INT DEFAULT NULL, ADD fee_paid TINYINT(1) DEFAULT NULL, DROP size, DROP complainant, DROP complainant_address, DROP complainant_postal_code, DROP case_state, CHANGE complainant_phone complainant_phone INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE case_entity DROP FOREIGN KEY FK_A7C603C1F4BD7827');
        $this->addSql('DROP INDEX IDX_A7C603C1F4BD7827 ON case_entity');
        $this->addSql('ALTER TABLE case_entity DROP assigned_to_id, DROP complainant, DROP complainant_street_name_and_number, DROP complainant_zip, DROP complainant_city, DROP complainant_cpr');
        $this->addSql('ALTER TABLE resident_complaint_board_case ADD size INT NOT NULL, ADD complainant_address VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD complainant_postal_code VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD case_state VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'submitted\' NOT NULL COLLATE `utf8mb4_unicode_ci`, DROP lease_size, DROP has_vacated, DROP lease_street_name_and_number, DROP lease_zip, DROP lease_city, DROP lease_started, DROP lease_agreed_rent, DROP lease_regulated_rent, DROP lease_rent_at_collection_time, DROP lease_security_deposit, DROP previous_cases_at_lease, DROP prepaid_rent, DROP fee_paid, CHANGE complainant_phone complainant_phone INT DEFAULT NULL, CHANGE lease_interior_maintenance complainant VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
