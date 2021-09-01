<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210901091254 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE case_entity ADD complainant VARCHAR(255) NOT NULL, ADD complainant_address VARCHAR(255) NOT NULL, ADD complainant_zip VARCHAR(255) NOT NULL, ADD case_state VARCHAR(255) DEFAULT \'submitted\' NOT NULL, ADD complainant_cpr VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE fence_review_case DROP complainant, DROP complainant_address, DROP complainant_zip, DROP case_state, DROP complainant_cpr');
        $this->addSql('ALTER TABLE rent_board_case DROP complainant, DROP complainant_address, DROP complainant_zip, DROP case_state, DROP complainant_cpr');
        $this->addSql('ALTER TABLE resident_complaint_board_case DROP complainant, DROP complainant_address, DROP complainant_zip, DROP case_state, DROP complainant_cpr');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE case_entity DROP complainant, DROP complainant_address, DROP complainant_zip, DROP case_state, DROP complainant_cpr');
        $this->addSql('ALTER TABLE fence_review_case ADD complainant VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, ADD complainant_address VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, ADD complainant_zip VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, ADD case_state VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'submitted\' NOT NULL COLLATE `utf8mb4_unicode_ci`, ADD complainant_cpr VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE rent_board_case ADD complainant VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, ADD complainant_address VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, ADD complainant_zip VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, ADD case_state VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'submitted\' NOT NULL COLLATE `utf8mb4_unicode_ci`, ADD complainant_cpr VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE resident_complaint_board_case ADD complainant VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, ADD complainant_address VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, ADD complainant_zip VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, ADD case_state VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'submitted\' NOT NULL COLLATE `utf8mb4_unicode_ci`, ADD complainant_cpr VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
