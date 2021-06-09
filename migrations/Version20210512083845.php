<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210512083845 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE resident_complaint_board_case ADD complainant_address VARCHAR(255) DEFAULT NULL, ADD complainant_phone INT DEFAULT NULL, ADD complainant_postal_code VARCHAR(255) DEFAULT NULL, ADD case_state VARCHAR(255) DEFAULT \'submitted\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE resident_complaint_board_case DROP complainant_address, DROP complainant_phone, DROP complainant_postal_code, DROP case_state');
    }
}
