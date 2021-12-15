<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211215084311 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE case_entity CHANGE complainant_address_number complainant_address_number VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE fence_review_case CHANGE accused_address_number accused_address_number VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE rent_board_case CHANGE lease_address_number lease_address_number VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE resident_complaint_board_case CHANGE lease_address_number lease_address_number VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE case_entity CHANGE complainant_address_number complainant_address_number INT NOT NULL');
        $this->addSql('ALTER TABLE fence_review_case CHANGE accused_address_number accused_address_number INT NOT NULL');
        $this->addSql('ALTER TABLE rent_board_case CHANGE lease_address_number lease_address_number INT NOT NULL');
        $this->addSql('ALTER TABLE resident_complaint_board_case CHANGE lease_address_number lease_address_number INT NOT NULL');
    }
}
