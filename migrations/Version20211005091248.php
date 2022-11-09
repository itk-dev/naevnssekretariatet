<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211005091248 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE agenda ADD protocol_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE agenda ADD CONSTRAINT FK_2CEDC877CCD59258 FOREIGN KEY (protocol_id) REFERENCES agenda_protocol (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2CEDC877CCD59258 ON agenda (protocol_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE agenda DROP FOREIGN KEY FK_2CEDC877CCD59258');
        $this->addSql('DROP INDEX UNIQ_2CEDC877CCD59258 ON agenda');
        $this->addSql('ALTER TABLE agenda DROP protocol_id');
    }
}
