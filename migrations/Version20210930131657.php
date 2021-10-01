<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210930131657 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE case_presentation (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', presentation LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE agenda_case_item ADD presentation_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE agenda_case_item ADD CONSTRAINT FK_D4DD0259AB627E8B FOREIGN KEY (presentation_id) REFERENCES case_presentation (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D4DD0259AB627E8B ON agenda_case_item (presentation_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE agenda_case_item DROP FOREIGN KEY FK_D4DD0259AB627E8B');
        $this->addSql('DROP TABLE case_presentation');
        $this->addSql('DROP INDEX UNIQ_D4DD0259AB627E8B ON agenda_case_item');
        $this->addSql('ALTER TABLE agenda_case_item DROP presentation_id');
    }
}
