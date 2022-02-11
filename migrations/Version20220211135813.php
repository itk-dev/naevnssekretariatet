<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220211135813 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE hearing ADD started_on DATE DEFAULT NULL, ADD finished_on DATE DEFAULT NULL, DROP start_date, DROP finish_date');
        $this->addSql('ALTER TABLE hearing_post CHANGE forward_date forwarded_on DATE DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE hearing ADD start_date DATE DEFAULT NULL, ADD finish_date DATE DEFAULT NULL, DROP started_on, DROP finished_on');
        $this->addSql('ALTER TABLE hearing_post CHANGE forwarded_on forward_date DATE DEFAULT NULL');
    }
}
