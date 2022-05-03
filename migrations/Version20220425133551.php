<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220425133551 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE agenda_item ADD title VARCHAR(255) NOT NULL');
        $this->addSql('UPDATE agenda_item JOIN agenda_case_item ON agenda_case_item.id = agenda_item.id SET agenda_item.title = agenda_case_item.title');
        $this->addSql('ALTER TABLE agenda_case_item DROP title');
        $this->addSql('UPDATE agenda_item JOIN agenda_manuel_item ON agenda_manuel_item.id = agenda_item.id SET agenda_item.title = agenda_manuel_item.title');
        $this->addSql('ALTER TABLE agenda_manuel_item DROP title');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE agenda_case_item ADD title VARCHAR(255) NOT NULL');
        $this->addSql('UPDATE agenda_case_item JOIN agenda_item ON agenda_item.id = agenda_case_item.id SET agenda_case_item.title = agenda_item.title');
        $this->addSql('ALTER TABLE agenda_manuel_item ADD title VARCHAR(255) NOT NULL');
        $this->addSql('UPDATE agenda_manuel_item JOIN agenda_item ON agenda_item.id = agenda_manuel_item.id SET agenda_manuel_item.title = agenda_item.title');
        $this->addSql('ALTER TABLE agenda_item DROP title');
    }
}
