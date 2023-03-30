<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230330110018 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE digital_post_envelope ADD errors LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('UPDATE digital_post_envelope SET errors = \'[]\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE digital_post_envelope DROP errors');
    }
}
