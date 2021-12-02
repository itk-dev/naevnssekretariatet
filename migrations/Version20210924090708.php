<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210924090708 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE agenda_case_item (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', case_entity_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', inspection TINYINT(1) DEFAULT \'0\' NOT NULL, INDEX IDX_D4DD0259AF060DA6 (case_entity_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE agenda_item (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', start_time VARCHAR(255) NOT NULL, end_time VARCHAR(255) NOT NULL, meeting_point VARCHAR(255) NOT NULL, discr VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE agenda_manuel_item (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE agenda_case_item ADD CONSTRAINT FK_D4DD0259AF060DA6 FOREIGN KEY (case_entity_id) REFERENCES case_entity (id)');
        $this->addSql('ALTER TABLE agenda_case_item ADD CONSTRAINT FK_D4DD0259BF396750 FOREIGN KEY (id) REFERENCES agenda_item (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE agenda_manuel_item ADD CONSTRAINT FK_2F8B2EE2BF396750 FOREIGN KEY (id) REFERENCES agenda_item (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE agenda_case_item DROP FOREIGN KEY FK_D4DD0259BF396750');
        $this->addSql('ALTER TABLE agenda_manuel_item DROP FOREIGN KEY FK_2F8B2EE2BF396750');
        $this->addSql('DROP TABLE agenda_case_item');
        $this->addSql('DROP TABLE agenda_item');
        $this->addSql('DROP TABLE agenda_manuel_item');
    }
}
