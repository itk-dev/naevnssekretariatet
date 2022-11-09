<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211115114118 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE board_member ADD municipality_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE board_member ADD CONSTRAINT FK_DCFABEDFAE6F181C FOREIGN KEY (municipality_id) REFERENCES municipality (id)');
        $this->addSql('CREATE INDEX IDX_DCFABEDFAE6F181C ON board_member (municipality_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE board_member DROP FOREIGN KEY FK_DCFABEDFAE6F181C');
        $this->addSql('DROP INDEX IDX_DCFABEDFAE6F181C ON board_member');
        $this->addSql('ALTER TABLE board_member DROP municipality_id');
    }
}
