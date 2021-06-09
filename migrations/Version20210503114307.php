<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210503114307 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE settings');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE settings (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', municipality_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', deadline INT NOT NULL, UNIQUE INDEX UNIQ_E545A0C5AE6F181C (municipality_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE settings ADD CONSTRAINT FK_E545A0C5AE6F181C FOREIGN KEY (municipality_id) REFERENCES municipality (id)');
    }
}
