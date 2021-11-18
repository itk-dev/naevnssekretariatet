<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211118105606 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE case_entity ADD complainant_address_street VARCHAR(255) NOT NULL, ADD complainant_address_number INT NOT NULL, ADD complainant_address_floor VARCHAR(255) DEFAULT NULL, ADD complainant_address_side VARCHAR(255) DEFAULT NULL, ADD complainant_address_postal_code INT NOT NULL, ADD complainant_address_city VARCHAR(255) NOT NULL, DROP complainant_street_name_and_number, DROP complainant_zip, DROP complainant_city');
        $this->addSql('ALTER TABLE fence_review_case ADD accused_address_street VARCHAR(255) NOT NULL, ADD accused_address_number INT NOT NULL, ADD accused_address_floor VARCHAR(255) DEFAULT NULL, ADD accused_address_side VARCHAR(255) DEFAULT NULL, ADD accused_address_postal_code INT NOT NULL, ADD accused_address_city VARCHAR(255) NOT NULL, DROP accused_street_name_and_number, DROP accused_zip, DROP accused_city');
        $this->addSql('ALTER TABLE rent_board_case ADD lease_address_street VARCHAR(255) NOT NULL, ADD lease_address_number INT NOT NULL, ADD lease_address_floor VARCHAR(255) DEFAULT NULL, ADD lease_address_side VARCHAR(255) DEFAULT NULL, ADD lease_address_postal_code INT NOT NULL, ADD lease_address_city VARCHAR(255) NOT NULL, DROP lease_street_name_and_number, DROP lease_zip, DROP lease_city');
        $this->addSql('ALTER TABLE resident_complaint_board_case ADD lease_address_street VARCHAR(255) NOT NULL, ADD lease_address_number INT NOT NULL, ADD lease_address_floor VARCHAR(255) DEFAULT NULL, ADD lease_address_side VARCHAR(255) DEFAULT NULL, ADD lease_address_postal_code INT NOT NULL, ADD lease_address_city VARCHAR(255) NOT NULL, DROP lease_street_name_and_number, DROP lease_zip, DROP lease_city');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE case_entity ADD complainant_street_name_and_number VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, ADD complainant_zip VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, ADD complainant_city VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, DROP complainant_address_street, DROP complainant_address_number, DROP complainant_address_floor, DROP complainant_address_side, DROP complainant_address_postal_code, DROP complainant_address_city');
        $this->addSql('ALTER TABLE fence_review_case ADD accused_street_name_and_number VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, ADD accused_zip VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, ADD accused_city VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, DROP accused_address_street, DROP accused_address_number, DROP accused_address_floor, DROP accused_address_side, DROP accused_address_postal_code, DROP accused_address_city');
        $this->addSql('ALTER TABLE rent_board_case ADD lease_street_name_and_number VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, ADD lease_zip VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, ADD lease_city VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, DROP lease_address_street, DROP lease_address_number, DROP lease_address_floor, DROP lease_address_side, DROP lease_address_postal_code, DROP lease_address_city');
        $this->addSql('ALTER TABLE resident_complaint_board_case ADD lease_street_name_and_number VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, ADD lease_zip VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, ADD lease_city VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, DROP lease_address_street, DROP lease_address_number, DROP lease_address_floor, DROP lease_address_side, DROP lease_address_postal_code, DROP lease_address_city');
    }
}
