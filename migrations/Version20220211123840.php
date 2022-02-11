<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220211123840 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE hearing ADD finish_date DATE DEFAULT NULL, DROP has_been_started, DROP has_finished');
        $this->addSql('ALTER TABLE hearing_post ADD forward_date DATE DEFAULT NULL, DROP has_been_processed_and_forwarded');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE hearing ADD has_been_started TINYINT(1) DEFAULT \'0\' NOT NULL, ADD has_finished TINYINT(1) NOT NULL, DROP finish_date');
        $this->addSql('ALTER TABLE hearing_post ADD has_been_processed_and_forwarded TINYINT(1) DEFAULT \'0\' NOT NULL, DROP forward_date');
    }
}
