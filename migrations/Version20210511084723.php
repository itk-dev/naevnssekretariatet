<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210511084723 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE board_member DROP FOREIGN KEY FK_DCFABEDFE7EC5785');
        $this->addSql('DROP INDEX IDX_DCFABEDFE7EC5785 ON board_member');
        $this->addSql('ALTER TABLE board_member DROP board_id');
        $this->addSql('ALTER TABLE case_entity CHANGE created_at created_at DATETIME NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE board_member ADD board_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE board_member ADD CONSTRAINT FK_DCFABEDFE7EC5785 FOREIGN KEY (board_id) REFERENCES board (id)');
        $this->addSql('CREATE INDEX IDX_DCFABEDFE7EC5785 ON board_member (board_id)');
        $this->addSql('ALTER TABLE case_entity CHANGE created_at created_at DATETIME DEFAULT NULL');
    }
}
