<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220418173142 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE inspection_letter (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', template_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', document_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', agenda_case_item_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', title VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_BCCEA9A75DA0FB8 (template_id), INDEX IDX_BCCEA9A7C33F7837 (document_id), INDEX IDX_BCCEA9A7CF781C30 (agenda_case_item_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE inspection_letter_party (inspection_letter_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', party_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_2055E08CB96F667E (inspection_letter_id), INDEX IDX_2055E08C213C1059 (party_id), PRIMARY KEY(inspection_letter_id, party_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE inspection_letter ADD CONSTRAINT FK_BCCEA9A75DA0FB8 FOREIGN KEY (template_id) REFERENCES mail_template (id)');
        $this->addSql('ALTER TABLE inspection_letter ADD CONSTRAINT FK_BCCEA9A7C33F7837 FOREIGN KEY (document_id) REFERENCES document (id)');
        $this->addSql('ALTER TABLE inspection_letter ADD CONSTRAINT FK_BCCEA9A7CF781C30 FOREIGN KEY (agenda_case_item_id) REFERENCES agenda_case_item (id)');
        $this->addSql('ALTER TABLE inspection_letter_party ADD CONSTRAINT FK_2055E08CB96F667E FOREIGN KEY (inspection_letter_id) REFERENCES inspection_letter (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE inspection_letter_party ADD CONSTRAINT FK_2055E08C213C1059 FOREIGN KEY (party_id) REFERENCES party (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE inspection_letter_party DROP FOREIGN KEY FK_2055E08CB96F667E');
        $this->addSql('DROP TABLE inspection_letter');
        $this->addSql('DROP TABLE inspection_letter_party');
    }
}
