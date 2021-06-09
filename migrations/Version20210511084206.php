<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210511084206 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE case_entity CHANGE created_at created_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE resident_complaint_board_case CHANGE size size INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE case_entity CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE resident_complaint_board_case CHANGE size size INT DEFAULT NULL');
    }
}
