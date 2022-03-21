<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220321094447 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE case_entity ADD created_by_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', ADD updated_by_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE case_entity ADD CONSTRAINT FK_A7C603C1B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE case_entity ADD CONSTRAINT FK_A7C603C1896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_A7C603C1B03A8386 ON case_entity (created_by_id)');
        $this->addSql('CREATE INDEX IDX_A7C603C1896DBBDE ON case_entity (updated_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE case_entity DROP FOREIGN KEY FK_A7C603C1B03A8386');
        $this->addSql('ALTER TABLE case_entity DROP FOREIGN KEY FK_A7C603C1896DBBDE');
        $this->addSql('DROP INDEX IDX_A7C603C1B03A8386 ON case_entity');
        $this->addSql('DROP INDEX IDX_A7C603C1896DBBDE ON case_entity');
        $this->addSql('ALTER TABLE case_entity DROP created_by_id, DROP updated_by_id, CHANGE case_number case_number VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE current_place current_place VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE complainant complainant VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE sorting_address sorting_address VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE sorting_complainant sorting_complainant VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE sorting_counterparty sorting_counterparty VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE removal_reason removal_reason LONGTEXT DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE complainant_address_street complainant_address_street VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE complainant_address_number complainant_address_number VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE complainant_address_floor complainant_address_floor VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE complainant_address_side complainant_address_side VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE complainant_address_city complainant_address_city VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE complainant_address_bbr_data complainant_address_bbr_data LONGTEXT DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:json)\', CHANGE complainant_identification_type complainant_identification_type VARCHAR(32) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE complainant_identification_identifier complainant_identification_identifier VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE discr discr VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
