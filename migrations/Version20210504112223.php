<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210504112223 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE case_entity (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', board_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', complainant VARCHAR(255) DEFAULT NULL, INDEX IDX_A7C603C1E7EC5785 (board_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE resident_complaint_board_case (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', size INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE case_entity ADD CONSTRAINT FK_A7C603C1E7EC5785 FOREIGN KEY (board_id) REFERENCES board (id)');
        $this->addSql('ALTER TABLE board ADD case_form_type VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE case_entity');
        $this->addSql('DROP TABLE resident_complaint_board_case');
        $this->addSql('ALTER TABLE board DROP case_form_type');
    }
}
