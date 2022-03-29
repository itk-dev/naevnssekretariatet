<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220329090040 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE party ADD identification_p_number VARCHAR(10) DEFAULT NULL, ADD identification_validated_at DATETIME DEFAULT NULL, CHANGE identifier_type identification_type VARCHAR(32) NOT NULL, CHANGE identifier identification_identifier VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE party ADD identifier VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, ADD identifier_type VARCHAR(32) NOT NULL COLLATE `utf8mb4_unicode_ci`, DROP identification_type, DROP identification_identifier, DROP identification_p_number, DROP identification_validated_at');
    }
}
