<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220303102223 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE digital_post ADD next_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', ADD subject VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE digital_post ADD CONSTRAINT FK_383EDC2AA23F6C8 FOREIGN KEY (next_id) REFERENCES digital_post (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_383EDC2AA23F6C8 ON digital_post (next_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE digital_post DROP FOREIGN KEY FK_383EDC2AA23F6C8');
        $this->addSql('DROP INDEX UNIQ_383EDC2AA23F6C8 ON digital_post');
        $this->addSql('ALTER TABLE digital_post DROP next_id, DROP subject');
    }
}
