<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230321124039 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE digital_post_envelope ADD forsendelse_uuid VARCHAR(36) DEFAULT NULL, ADD forsendelse LONGTEXT DEFAULT NULL, CHANGE message_uuid me_mo_message_uuid VARCHAR(36) DEFAULT NULL, CHANGE message me_mo_message LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE digital_post_envelope DROP forsendelse_uuid, DROP forsendelse, CHANGE me_mo_message_uuid message_uuid VARCHAR(36) DEFAULT NULL, CHANGE me_mo_message message LONGTEXT DEFAULT NULL');
    }
}
