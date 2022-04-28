<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220428132757 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE case_entity ADD bringer_is_under_address_protection TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE fence_review_case ADD accused_is_under_address_protection TINYINT(1) DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE case_entity DROP bringer_is_under_address_protection');
        $this->addSql('ALTER TABLE fence_review_case DROP accused_is_under_address_protection');
    }
}
