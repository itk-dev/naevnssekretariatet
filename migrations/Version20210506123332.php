<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210506123332 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE case_entity ADD discr VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE resident_complaint_board_case DROP complainant');
        $this->addSql('ALTER TABLE resident_complaint_board_case ADD CONSTRAINT FK_35E1CB13BF396750 FOREIGN KEY (id) REFERENCES case_entity (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE case_entity DROP discr');
        $this->addSql('ALTER TABLE resident_complaint_board_case DROP FOREIGN KEY FK_35E1CB13BF396750');
        $this->addSql('ALTER TABLE resident_complaint_board_case ADD complainant VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
