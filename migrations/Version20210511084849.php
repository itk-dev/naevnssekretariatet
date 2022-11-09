<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210511084849 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE board_member ADD board_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE board_member ADD CONSTRAINT FK_DCFABEDFE7EC5785 FOREIGN KEY (board_id) REFERENCES sub_board (id)');
        $this->addSql('CREATE INDEX IDX_DCFABEDFE7EC5785 ON board_member (board_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE board_member DROP FOREIGN KEY FK_DCFABEDFE7EC5785');
        $this->addSql('DROP INDEX IDX_DCFABEDFE7EC5785 ON board_member');
        $this->addSql('ALTER TABLE board_member DROP board_id');
    }
}
