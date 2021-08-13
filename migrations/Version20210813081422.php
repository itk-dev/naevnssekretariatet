<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210813081422 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE case_entity ADD complaint_category_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', DROP case_type');
        $this->addSql('ALTER TABLE case_entity ADD CONSTRAINT FK_A7C603C1D0DA653B FOREIGN KEY (complaint_category_id) REFERENCES complaint_category (id)');
        $this->addSql('CREATE INDEX IDX_A7C603C1D0DA653B ON case_entity (complaint_category_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE case_entity DROP FOREIGN KEY FK_A7C603C1D0DA653B');
        $this->addSql('DROP INDEX IDX_A7C603C1D0DA653B ON case_entity');
        $this->addSql('ALTER TABLE case_entity ADD case_type VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, DROP complaint_category_id');
    }
}
