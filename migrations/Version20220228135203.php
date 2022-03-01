<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220228135203 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE hearing_post DROP FOREIGN KEY FK_54293ABF5DA0FB8');
        $this->addSql('ALTER TABLE hearing_post DROP FOREIGN KEY FK_54293ABFE92F8F78');
        $this->addSql('DROP INDEX IDX_54293ABFE92F8F78 ON hearing_post');
        $this->addSql('DROP INDEX IDX_54293ABF5DA0FB8 ON hearing_post');
        $this->addSql('ALTER TABLE hearing_post DROP recipient_id, DROP template_id, DROP forwarded_on, DROP title');
        $this->addSql('ALTER TABLE hearing_post_request ADD recipient_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', ADD template_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', ADD forwarded_on DATE DEFAULT NULL, ADD title VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE hearing_post_request ADD CONSTRAINT FK_A12A6D00E92F8F78 FOREIGN KEY (recipient_id) REFERENCES party (id)');
        $this->addSql('ALTER TABLE hearing_post_request ADD CONSTRAINT FK_A12A6D005DA0FB8 FOREIGN KEY (template_id) REFERENCES mail_template (id)');
        $this->addSql('CREATE INDEX IDX_A12A6D00E92F8F78 ON hearing_post_request (recipient_id)');
        $this->addSql('CREATE INDEX IDX_A12A6D005DA0FB8 ON hearing_post_request (template_id)');
        $this->addSql('ALTER TABLE hearing_post_response ADD sender_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', ADD approved_on DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE hearing_post_response ADD CONSTRAINT FK_5E5138CCF624B39D FOREIGN KEY (sender_id) REFERENCES party (id)');
        $this->addSql('CREATE INDEX IDX_5E5138CCF624B39D ON hearing_post_response (sender_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE hearing_post ADD recipient_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', ADD template_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', ADD forwarded_on DATE DEFAULT NULL, ADD title VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE hearing_post ADD CONSTRAINT FK_54293ABF5DA0FB8 FOREIGN KEY (template_id) REFERENCES mail_template (id)');
        $this->addSql('ALTER TABLE hearing_post ADD CONSTRAINT FK_54293ABFE92F8F78 FOREIGN KEY (recipient_id) REFERENCES party (id)');
        $this->addSql('CREATE INDEX IDX_54293ABFE92F8F78 ON hearing_post (recipient_id)');
        $this->addSql('CREATE INDEX IDX_54293ABF5DA0FB8 ON hearing_post (template_id)');
        $this->addSql('ALTER TABLE hearing_post_request DROP FOREIGN KEY FK_A12A6D00E92F8F78');
        $this->addSql('ALTER TABLE hearing_post_request DROP FOREIGN KEY FK_A12A6D005DA0FB8');
        $this->addSql('DROP INDEX IDX_A12A6D00E92F8F78 ON hearing_post_request');
        $this->addSql('DROP INDEX IDX_A12A6D005DA0FB8 ON hearing_post_request');
        $this->addSql('ALTER TABLE hearing_post_request DROP recipient_id, DROP template_id, DROP forwarded_on, DROP title');
        $this->addSql('ALTER TABLE hearing_post_response DROP FOREIGN KEY FK_5E5138CCF624B39D');
        $this->addSql('DROP INDEX IDX_5E5138CCF624B39D ON hearing_post_response');
        $this->addSql('ALTER TABLE hearing_post_response DROP sender_id, DROP approved_on');
    }
}
