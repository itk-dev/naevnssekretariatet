<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220815135704 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE board ADD hearing_post_response_template_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE board ADD CONSTRAINT FK_58562B477026F51C FOREIGN KEY (hearing_post_response_template_id) REFERENCES mail_template (id)');
        $this->addSql('CREATE INDEX IDX_58562B477026F51C ON board (hearing_post_response_template_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE board DROP FOREIGN KEY FK_58562B477026F51C');
        $this->addSql('DROP INDEX IDX_58562B477026F51C ON board');
        $this->addSql('ALTER TABLE board DROP hearing_post_response_template_id');
    }
}
