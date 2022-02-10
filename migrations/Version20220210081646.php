<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220210081646 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE hearing (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', complainant_has_no_more_to_add TINYINT(1) DEFAULT \'0\' NOT NULL, counterpart_has_no_more_to_add TINYINT(1) DEFAULT \'0\' NOT NULL, has_new_hearing_post TINYINT(1) DEFAULT \'0\' NOT NULL, has_been_started TINYINT(1) DEFAULT \'0\' NOT NULL, start_date DATE DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE hearing_post (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', hearing_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', sender_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', recipient_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', content LONGTEXT NOT NULL, has_been_processed_and_forwarded TINYINT(1) DEFAULT \'0\' NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_54293ABF66F0CD18 (hearing_id), INDEX IDX_54293ABFF624B39D (sender_id), INDEX IDX_54293ABFE92F8F78 (recipient_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE hearing_post ADD CONSTRAINT FK_54293ABF66F0CD18 FOREIGN KEY (hearing_id) REFERENCES hearing (id)');
        $this->addSql('ALTER TABLE hearing_post ADD CONSTRAINT FK_54293ABFF624B39D FOREIGN KEY (sender_id) REFERENCES party (id)');
        $this->addSql('ALTER TABLE hearing_post ADD CONSTRAINT FK_54293ABFE92F8F78 FOREIGN KEY (recipient_id) REFERENCES party (id)');
        $this->addSql('ALTER TABLE case_entity ADD hearing_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE case_entity ADD CONSTRAINT FK_A7C603C166F0CD18 FOREIGN KEY (hearing_id) REFERENCES hearing (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A7C603C166F0CD18 ON case_entity (hearing_id)');
        $this->addSql('ALTER TABLE document ADD hearing_post_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A76CF583A5C FOREIGN KEY (hearing_post_id) REFERENCES hearing_post (id)');
        $this->addSql('CREATE INDEX IDX_D8698A76CF583A5C ON document (hearing_post_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE case_entity DROP FOREIGN KEY FK_A7C603C166F0CD18');
        $this->addSql('ALTER TABLE hearing_post DROP FOREIGN KEY FK_54293ABF66F0CD18');
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A76CF583A5C');
        $this->addSql('DROP TABLE hearing');
        $this->addSql('DROP TABLE hearing_post');
        $this->addSql('DROP INDEX UNIQ_A7C603C166F0CD18 ON case_entity');
        $this->addSql('ALTER TABLE case_entity DROP hearing_id');
        $this->addSql('DROP INDEX IDX_D8698A76CF583A5C ON document');
        $this->addSql('ALTER TABLE document DROP hearing_post_id');
    }
}
