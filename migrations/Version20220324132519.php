<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220324132519 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE case_entity ADD complainant_address_extra_address_information VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE fence_review_case ADD accused_address_extra_address_information VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE party ADD address_extra_address_information VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE recipient ADD address_extra_address_information VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE rent_board_case ADD lease_address_extra_address_information VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE resident_complaint_board_case ADD lease_address_extra_address_information VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE case_entity DROP complainant_address_extra_address_information');
        $this->addSql('ALTER TABLE fence_review_case DROP accused_address_extra_address_information');
        $this->addSql('ALTER TABLE party DROP address_extra_address_information');
        $this->addSql('ALTER TABLE recipient DROP address_extra_address_information');
        $this->addSql('ALTER TABLE rent_board_case DROP lease_address_extra_address_information');
        $this->addSql('ALTER TABLE resident_complaint_board_case DROP lease_address_extra_address_information');
    }
}
