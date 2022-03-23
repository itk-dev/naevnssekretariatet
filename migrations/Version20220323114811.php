<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220323114811 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE board_member_board');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE board_member_board (board_member_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', board_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_F3B9E5C3C7BA2FD5 (board_member_id), INDEX IDX_F3B9E5C3E7EC5785 (board_id), PRIMARY KEY(board_member_id, board_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE board_member_board ADD CONSTRAINT FK_F3B9E5C3E7EC5785 FOREIGN KEY (board_id) REFERENCES board (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE board_member_board ADD CONSTRAINT FK_F3B9E5C3C7BA2FD5 FOREIGN KEY (board_member_id) REFERENCES board_member (id) ON DELETE CASCADE');
    }
}
