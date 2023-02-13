<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230201141728 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A76CF583A5C');
        $this->addSql('DROP INDEX IDX_D8698A76CF583A5C ON document');
        $this->addSql('ALTER TABLE document DROP hearing_post_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE document ADD hearing_post_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A76CF583A5C FOREIGN KEY (hearing_post_id) REFERENCES hearing_post (id)');
        $this->addSql('CREATE INDEX IDX_D8698A76CF583A5C ON document (hearing_post_id)');
    }
}
