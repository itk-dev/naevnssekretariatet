<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220523081200 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE case_entity CHANGE bringer_identification_identifier bringer_identification_identifier VARCHAR(255) DEFAULT NULL, CHANGE bringer_identification_type bringer_identification_type VARCHAR(32) DEFAULT NULL');
        $this->addSql('ALTER TABLE fence_review_case CHANGE accused_identification_identifier accused_identification_identifier VARCHAR(255) DEFAULT NULL, CHANGE accused_identification_type accused_identification_type VARCHAR(32) DEFAULT NULL');
        $this->addSql('ALTER TABLE party CHANGE identification_identifier identification_identifier VARCHAR(255) DEFAULT NULL, CHANGE identification_type identification_type VARCHAR(32) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE case_entity CHANGE bringer_identification_identifier bringer_identification_identifier VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE fence_review_case CHANGE accused_identification_identifier accused_identification_identifier VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE party CHANGE identification_identifier identification_identifier VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
