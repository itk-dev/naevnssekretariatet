<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230220163033 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE digital_post_envelope (status VARCHAR(255) NOT NULL, status_message VARCHAR(255) DEFAULT NULL, message_uuid VARCHAR(36) NOT NULL, digital_post_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', recipient_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', message LONGTEXT NOT NULL, receipt LONGTEXT NOT NULL, beskedfordeler_messages LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_47854A396B0BD826 (digital_post_id), INDEX IDX_47854A39E92F8F78 (recipient_id), PRIMARY KEY(message_uuid)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE digital_post_envelope ADD CONSTRAINT FK_47854A396B0BD826 FOREIGN KEY (digital_post_id) REFERENCES digital_post (id)');
        $this->addSql('ALTER TABLE digital_post_envelope ADD CONSTRAINT FK_47854A39E92F8F78 FOREIGN KEY (recipient_id) REFERENCES recipient (id)');
        $this->addSql('CREATE INDEX status ON digital_post_envelope (status)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX status ON digital_post_envelope');
        $this->addSql('ALTER TABLE digital_post_envelope DROP FOREIGN KEY FK_47854A396B0BD826');
        $this->addSql('ALTER TABLE digital_post_envelope DROP FOREIGN KEY FK_47854A39E92F8F78');
        $this->addSql('DROP TABLE digital_post_envelope');
    }
}
