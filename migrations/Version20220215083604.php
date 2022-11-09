<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220215083604 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE case_entity ADD complainant_identifier_type VARCHAR(32) NOT NULL, CHANGE complainant_cpr complainant_identifier VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE fence_review_case ADD accused_identifier_type VARCHAR(32) NOT NULL, CHANGE accused_cpr accused_identifier VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE case_entity ADD complainant_cpr VARCHAR(255) NOT NULL, DROP complainant_identifier_type, DROP complainant_identifier');
        $this->addSql('ALTER TABLE fence_review_case ADD accused_cpr VARCHAR(255) NOT NULL, DROP accused_identifier_type, DROP accused_identifier');
    }
}
