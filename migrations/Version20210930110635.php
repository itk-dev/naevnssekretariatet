<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210930110635 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE agenda_case_item_document (agenda_case_item_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', document_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_29C603FACF781C30 (agenda_case_item_id), INDEX IDX_29C603FAC33F7837 (document_id), PRIMARY KEY(agenda_case_item_id, document_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE agenda_case_item_document ADD CONSTRAINT FK_29C603FACF781C30 FOREIGN KEY (agenda_case_item_id) REFERENCES agenda_case_item (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE agenda_case_item_document ADD CONSTRAINT FK_29C603FAC33F7837 FOREIGN KEY (document_id) REFERENCES document (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE agenda_case_item_document');
    }
}
