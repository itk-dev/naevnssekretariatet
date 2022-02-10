<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220210084619 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE hearing_post ADD template_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE hearing_post ADD CONSTRAINT FK_54293ABF5DA0FB8 FOREIGN KEY (template_id) REFERENCES mail_template (id)');
        $this->addSql('CREATE INDEX IDX_54293ABF5DA0FB8 ON hearing_post (template_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE hearing_post DROP FOREIGN KEY FK_54293ABF5DA0FB8');
        $this->addSql('DROP INDEX IDX_54293ABF5DA0FB8 ON hearing_post');
        $this->addSql('ALTER TABLE hearing_post DROP template_id');
    }
}
