<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220309132418 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE case_entity ADD complainant_identification_validated_at DATETIME DEFAULT NULL, CHANGE complainant_identifier_type complainant_identification_type VARCHAR(32) NOT NULL, CHANGE complainant_identifier complainant_identification_identifier VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE fence_review_case ADD accused_identification_validated_at DATETIME DEFAULT NULL, CHANGE accused_identifier_type accused_identification_type VARCHAR(32) NOT NULL, CHANGE accused_identifier accused_identification_identifier VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE case_entity ADD complainant_identifier VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, ADD complainant_identifier_type VARCHAR(32) NOT NULL COLLATE `utf8mb4_unicode_ci`, DROP complainant_identification_type, DROP complainant_identification_identifier, DROP complainant_identification_validated_at');
        $this->addSql('ALTER TABLE fence_review_case ADD accused_identifier VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, ADD accused_identifier_type VARCHAR(32) NOT NULL COLLATE `utf8mb4_unicode_ci`, DROP accused_identification_type, DROP accused_identification_identifier, DROP accused_identification_validated_at');
    }
}
