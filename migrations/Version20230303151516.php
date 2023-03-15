<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230303151516 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE digital_post DROP status');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE digital_post ADD status VARCHAR(32) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
