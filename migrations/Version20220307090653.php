<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220307090653 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE hearing_post_request (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', recipient_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', template_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', forwarded_on DATE DEFAULT NULL, title VARCHAR(255) NOT NULL, INDEX IDX_A12A6D00E92F8F78 (recipient_id), INDEX IDX_A12A6D005DA0FB8 (template_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE hearing_post_response (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', sender_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', approved_on DATE DEFAULT NULL, INDEX IDX_5E5138CCF624B39D (sender_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE hearing_post_request ADD CONSTRAINT FK_A12A6D00E92F8F78 FOREIGN KEY (recipient_id) REFERENCES party (id)');
        $this->addSql('ALTER TABLE hearing_post_request ADD CONSTRAINT FK_A12A6D005DA0FB8 FOREIGN KEY (template_id) REFERENCES mail_template (id)');
        $this->addSql('ALTER TABLE hearing_post_request ADD CONSTRAINT FK_A12A6D00BF396750 FOREIGN KEY (id) REFERENCES hearing_post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE hearing_post_response ADD CONSTRAINT FK_5E5138CCF624B39D FOREIGN KEY (sender_id) REFERENCES party (id)');
        $this->addSql('ALTER TABLE hearing_post_response ADD CONSTRAINT FK_5E5138CCBF396750 FOREIGN KEY (id) REFERENCES hearing_post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE case_entity ADD removal_reason LONGTEXT DEFAULT NULL, ADD soft_deleted TINYINT(1) DEFAULT 0 NOT NULL, ADD soft_deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE digital_post ADD next_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', ADD subject VARCHAR(255) NOT NULL, ADD total_file_size INT NOT NULL');
        $this->addSql('ALTER TABLE digital_post ADD CONSTRAINT FK_383EDC2AA23F6C8 FOREIGN KEY (next_id) REFERENCES digital_post (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_383EDC2AA23F6C8 ON digital_post (next_id)');
        $this->addSql('ALTER TABLE hearing_post DROP FOREIGN KEY FK_54293ABFE92F8F78');
        $this->addSql('ALTER TABLE hearing_post DROP FOREIGN KEY FK_54293ABF5DA0FB8');
        $this->addSql('DROP INDEX IDX_54293ABFE92F8F78 ON hearing_post');
        $this->addSql('DROP INDEX IDX_54293ABF5DA0FB8 ON hearing_post');
        $this->addSql('ALTER TABLE hearing_post DROP recipient_id, DROP template_id, DROP forwarded_on, CHANGE title discr VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE hearing_post_request');
        $this->addSql('DROP TABLE hearing_post_response');
        $this->addSql('ALTER TABLE case_entity DROP removal_reason, DROP soft_deleted, DROP soft_deleted_at');
        $this->addSql('ALTER TABLE digital_post DROP FOREIGN KEY FK_383EDC2AA23F6C8');
        $this->addSql('DROP INDEX UNIQ_383EDC2AA23F6C8 ON digital_post');
        $this->addSql('ALTER TABLE digital_post DROP next_id, DROP subject, DROP total_file_size');
        $this->addSql('ALTER TABLE fence_review_case CHANGE conditions conditions LONGTEXT NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE hearing_post ADD recipient_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', ADD template_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', ADD forwarded_on DATE DEFAULT NULL, ADD title VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, DROP discr');
        $this->addSql('ALTER TABLE hearing_post ADD CONSTRAINT FK_54293ABFE92F8F78 FOREIGN KEY (recipient_id) REFERENCES party (id)');
        $this->addSql('ALTER TABLE hearing_post ADD CONSTRAINT FK_54293ABF5DA0FB8 FOREIGN KEY (template_id) REFERENCES mail_template (id)');
        $this->addSql('CREATE INDEX IDX_54293ABFE92F8F78 ON hearing_post (recipient_id)');
        $this->addSql('CREATE INDEX IDX_54293ABF5DA0FB8 ON hearing_post (template_id)');
    }
}
