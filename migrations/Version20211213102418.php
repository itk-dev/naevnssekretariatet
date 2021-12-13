<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211213102418 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE case_entity ADD complainant_address_validated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE fence_review_case ADD accused_address_validated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE rent_board_case ADD lease_address_validated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE resident_complaint_board_case ADD lease_address_validated_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE case_entity DROP complainant_address_validated_at');
        $this->addSql('ALTER TABLE fence_review_case DROP accused_address_validated_at');
        $this->addSql('ALTER TABLE rent_board_case DROP lease_address_validated_at');
        $this->addSql('ALTER TABLE resident_complaint_board_case DROP lease_address_validated_at');
    }
}
