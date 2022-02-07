<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220207115408 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD board_member_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649C7BA2FD5 FOREIGN KEY (board_member_id) REFERENCES board_member (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649C7BA2FD5 ON user (board_member_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649C7BA2FD5');
        $this->addSql('DROP INDEX UNIQ_8D93D649C7BA2FD5 ON user');
        $this->addSql('ALTER TABLE user DROP board_member_id');
    }
}
