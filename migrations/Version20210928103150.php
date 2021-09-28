<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210928103150 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE board_role (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', board_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', title VARCHAR(255) NOT NULL, INDEX IDX_8DFFD349E7EC5785 (board_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE board_role_board_member (board_role_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', board_member_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_9E5BB1329E1D0AA3 (board_role_id), INDEX IDX_9E5BB132C7BA2FD5 (board_member_id), PRIMARY KEY(board_role_id, board_member_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE board_role ADD CONSTRAINT FK_8DFFD349E7EC5785 FOREIGN KEY (board_id) REFERENCES board (id)');
        $this->addSql('ALTER TABLE board_role_board_member ADD CONSTRAINT FK_9E5BB1329E1D0AA3 FOREIGN KEY (board_role_id) REFERENCES board_role (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE board_role_board_member ADD CONSTRAINT FK_9E5BB132C7BA2FD5 FOREIGN KEY (board_member_id) REFERENCES board_member (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE board_role_board_member DROP FOREIGN KEY FK_9E5BB1329E1D0AA3');
        $this->addSql('DROP TABLE board_role');
        $this->addSql('DROP TABLE board_role_board_member');
    }
}
