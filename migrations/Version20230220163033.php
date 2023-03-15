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
        $this->addSql('CREATE TABLE digital_post_envelope (message_uuid VARCHAR(36) NOT NULL, digital_post_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', recipient_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', status VARCHAR(255) NOT NULL, status_message VARCHAR(255) DEFAULT NULL, message LONGTEXT NOT NULL, receipt LONGTEXT NOT NULL, beskedfordeler_messages LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_15DBA4B76B0BD826 (digital_post_id), INDEX IDX_15DBA4B7E92F8F78 (recipient_id), INDEX status (status), PRIMARY KEY(message_uuid)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE digital_post_envelope ADD CONSTRAINT FK_15DBA4B76B0BD826 FOREIGN KEY (digital_post_id) REFERENCES digital_post (id)');
        $this->addSql('ALTER TABLE digital_post_envelope ADD CONSTRAINT FK_15DBA4B7E92F8F78 FOREIGN KEY (recipient_id) REFERENCES recipient (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE digital_post_envelope DROP FOREIGN KEY FK_15DBA4B76B0BD826');
        $this->addSql('ALTER TABLE digital_post_envelope DROP FOREIGN KEY FK_15DBA4B7E92F8F78');
        $this->addSql('DROP TABLE digital_post_envelope');
    }
}
