<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220506111917 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        // If leaseRegulatedRent is null we assume lease rent is NOT regulated
        $this->addSql('UPDATE rent_board_case SET rent_board_case.lease_regulated_rent = IF(rent_board_case.lease_regulated_rent IS NULL, 0, 1)');
        $this->addSql('ALTER TABLE rent_board_case CHANGE bringer_phone bringer_phone INT DEFAULT NULL, CHANGE lease_regulated_rent lease_regulated_rent TINYINT(1) DEFAULT NULL, CHANGE lease_type lease_type VARCHAR(255) DEFAULT NULL');
        $this->addSql('UPDATE resident_complaint_board_case SET resident_complaint_board_case.lease_regulated_rent = IF(resident_complaint_board_case.lease_regulated_rent IS NULL, 0, 1)');
        $this->addSql('ALTER TABLE resident_complaint_board_case CHANGE bringer_phone bringer_phone INT DEFAULT NULL, CHANGE lease_regulated_rent lease_regulated_rent TINYINT(1) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE rent_board_case CHANGE bringer_phone bringer_phone INT NOT NULL, CHANGE lease_regulated_rent lease_regulated_rent INT DEFAULT NULL, CHANGE lease_type lease_type VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE resident_complaint_board_case CHANGE bringer_phone bringer_phone INT NOT NULL, CHANGE lease_regulated_rent lease_regulated_rent INT DEFAULT NULL');
    }
}
