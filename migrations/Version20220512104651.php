<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220512104651 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Note we don't even try to convert existing UUIDs and timestamps to ULIDs.
        $this->addSql('ALTER TABLE log_entry CHANGE id id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE log_entry CHANGE id id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
    }
}
