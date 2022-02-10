<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220210094822 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE digital_post (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', document_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', entity_type VARCHAR(255) DEFAULT NULL, entity_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', status VARCHAR(32) DEFAULT NULL, data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', sent_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_383EDC2C33F7837 (document_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE digital_post_document (digital_post_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', document_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_472756A96B0BD826 (digital_post_id), INDEX IDX_472756A9C33F7837 (document_id), PRIMARY KEY(digital_post_id, document_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE recipient (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', digital_post_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) NOT NULL, identifier VARCHAR(255) NOT NULL, identifier_type VARCHAR(32) NOT NULL, address_street VARCHAR(255) NOT NULL, address_number VARCHAR(255) NOT NULL, address_floor VARCHAR(255) DEFAULT NULL, address_side VARCHAR(255) DEFAULT NULL, address_postal_code INT NOT NULL, address_city VARCHAR(255) NOT NULL, address_validated_at DATETIME DEFAULT NULL, address_bbr_data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', INDEX IDX_6804FB496B0BD826 (digital_post_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE digital_post ADD CONSTRAINT FK_383EDC2C33F7837 FOREIGN KEY (document_id) REFERENCES document (id)');
        $this->addSql('ALTER TABLE digital_post_document ADD CONSTRAINT FK_472756A96B0BD826 FOREIGN KEY (digital_post_id) REFERENCES digital_post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE digital_post_document ADD CONSTRAINT FK_472756A9C33F7837 FOREIGN KEY (document_id) REFERENCES document (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE recipient ADD CONSTRAINT FK_6804FB496B0BD826 FOREIGN KEY (digital_post_id) REFERENCES digital_post (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE digital_post_document DROP FOREIGN KEY FK_472756A96B0BD826');
        $this->addSql('ALTER TABLE recipient DROP FOREIGN KEY FK_6804FB496B0BD826');
        $this->addSql('DROP TABLE digital_post');
        $this->addSql('DROP TABLE digital_post_document');
        $this->addSql('DROP TABLE recipient');
        $this->addSql('ALTER TABLE agenda CHANGE remarks remarks LONGTEXT DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE agenda_case_item CHANGE title title VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE agenda_item CHANGE meeting_point meeting_point VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE discr discr VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE agenda_manuel_item CHANGE title title VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE description description LONGTEXT DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE agenda_protocol CHANGE protocol protocol LONGTEXT DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE bbr_data CHANGE address address VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE data data LONGTEXT NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE board CHANGE name name VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE case_form_type case_form_type VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE statuses statuses LONGTEXT NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE complainant_types complainant_types LONGTEXT NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE counterparty_types counterparty_types LONGTEXT NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE board_member CHANGE name name VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE cpr cpr VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE board_role CHANGE title title VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE case_decision_proposal CHANGE decision_proposal decision_proposal LONGTEXT DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE case_entity CHANGE case_number case_number VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE current_place current_place VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE complainant complainant VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE complainant_cpr complainant_cpr VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE sorting_address sorting_address VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE sorting_complainant sorting_complainant VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE sorting_counterparty sorting_counterparty VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE complainant_address_street complainant_address_street VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE complainant_address_number complainant_address_number VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE complainant_address_floor complainant_address_floor VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE complainant_address_side complainant_address_side VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE complainant_address_city complainant_address_city VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE complainant_address_bbr_data complainant_address_bbr_data LONGTEXT DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:json)\', CHANGE discr discr VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE case_parties CHANGE type type VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE case_presentation CHANGE presentation presentation LONGTEXT DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE complaint_category CHANGE name name VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE document CHANGE document_name document_name VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE type type VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE filename filename VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE fence_review_case CHANGE conditions conditions LONGTEXT NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE complainant_claim complainant_claim LONGTEXT NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE complainant_cadastral_number complainant_cadastral_number VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE accused accused VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE accused_cpr accused_cpr VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE accused_cadastral_number accused_cadastral_number VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE accused_address_street accused_address_street VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE accused_address_number accused_address_number VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE accused_address_floor accused_address_floor VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE accused_address_side accused_address_side VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE accused_address_city accused_address_city VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE accused_address_bbr_data accused_address_bbr_data LONGTEXT DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE log_entry CHANGE case_id case_id VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE entity_type entity_type VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE entity_id entity_id VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE action action VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE data data LONGTEXT NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:json)\', CHANGE user user VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE mail_template CHANGE name name VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE description description LONGTEXT NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE template_filename template_filename VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE type type VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE mail_template_macro CHANGE name name VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE macro macro VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE content content LONGTEXT NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE template_types template_types LONGTEXT DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE monolog_log_entry CHANGE message message LONGTEXT NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE context context LONGTEXT NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:json)\', CHANGE level_name level_name VARCHAR(50) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE channel channel VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE extra extra LONGTEXT NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:json)\', CHANGE formatted formatted LONGTEXT NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE municipality CHANGE name name VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE note CHANGE subject subject VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE content content LONGTEXT NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE party CHANGE address address VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE phone_number phone_number VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE journal_number journal_number VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE name name VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE cpr cpr VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE reminder CHANGE content content LONGTEXT NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE rent_board_case CHANGE lease_interior_maintenance lease_interior_maintenance VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE previous_cases_at_lease previous_cases_at_lease LONGTEXT DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE lease_type lease_type VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE lease_address_street lease_address_street VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE lease_address_number lease_address_number VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE lease_address_floor lease_address_floor VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE lease_address_side lease_address_side VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE lease_address_city lease_address_city VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE lease_address_bbr_data lease_address_bbr_data LONGTEXT DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE resident_complaint_board_case CHANGE lease_interior_maintenance lease_interior_maintenance VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE previous_cases_at_lease previous_cases_at_lease LONGTEXT DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE lease_address_street lease_address_street VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE lease_address_number lease_address_number VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE lease_address_floor lease_address_floor VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE lease_address_side lease_address_side VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE lease_address_city lease_address_city VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE lease_address_bbr_data lease_address_bbr_data LONGTEXT DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE uploaded_document_type CHANGE name name VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE user CHANGE email email VARCHAR(180) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE roles roles LONGTEXT NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:json)\', CHANGE login_token login_token VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE name name VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE initials initials VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
