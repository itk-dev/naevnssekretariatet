<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230302093859 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX `primary` ON digital_post_envelope');
        $this->addSql('ALTER TABLE digital_post_envelope ADD id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', CHANGE message_uuid message_uuid VARCHAR(36) DEFAULT NULL, CHANGE message message LONGTEXT DEFAULT NULL, CHANGE receipt receipt LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE digital_post_envelope ADD PRIMARY KEY (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX `PRIMARY` ON digital_post_envelope');
        $this->addSql('ALTER TABLE digital_post_envelope DROP id, CHANGE message_uuid message_uuid VARCHAR(36) NOT NULL, CHANGE message message LONGTEXT NOT NULL, CHANGE receipt receipt LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE digital_post_envelope ADD PRIMARY KEY (message_uuid)');
    }
}
