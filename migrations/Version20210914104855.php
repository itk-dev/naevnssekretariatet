<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210914104855 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE agenda_board_member (agenda_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', board_member_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_AE4E05BAEA67784A (agenda_id), INDEX IDX_AE4E05BAC7BA2FD5 (board_member_id), PRIMARY KEY(agenda_id, board_member_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE agenda_board_member ADD CONSTRAINT FK_AE4E05BAEA67784A FOREIGN KEY (agenda_id) REFERENCES agenda (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE agenda_board_member ADD CONSTRAINT FK_AE4E05BAC7BA2FD5 FOREIGN KEY (board_member_id) REFERENCES board_member (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE agenda ADD board_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', ADD date DATETIME DEFAULT NULL, ADD start VARCHAR(255) DEFAULT NULL, ADD end VARCHAR(255) DEFAULT NULL, ADD status VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE agenda ADD CONSTRAINT FK_2CEDC877E7EC5785 FOREIGN KEY (board_id) REFERENCES board (id)');
        $this->addSql('CREATE INDEX IDX_2CEDC877E7EC5785 ON agenda (board_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE agenda_board_member');
        $this->addSql('ALTER TABLE agenda DROP FOREIGN KEY FK_2CEDC877E7EC5785');
        $this->addSql('DROP INDEX IDX_2CEDC877E7EC5785 ON agenda');
        $this->addSql('ALTER TABLE agenda DROP board_id, DROP date, DROP start, DROP end, DROP status');
    }
}
