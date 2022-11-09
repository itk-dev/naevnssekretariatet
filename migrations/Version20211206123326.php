<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211206123326 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE board ADD finish_processing_deadline_default INT NOT NULL, ADD finish_hearing_deadline_default INT NOT NULL, CHANGE default_deadline hearing_response_deadline INT NOT NULL');
        $this->addSql('ALTER TABLE case_entity ADD finish_processing_deadline DATE NOT NULL, ADD finish_hearing_deadline DATE NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE board ADD default_deadline INT NOT NULL, DROP hearing_response_deadline, DROP finish_processing_deadline_default, DROP finish_hearing_deadline_default');
        $this->addSql('ALTER TABLE case_entity DROP finish_processing_deadline, DROP finish_hearing_deadline');
    }
}
