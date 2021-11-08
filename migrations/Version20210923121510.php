<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210923121510 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE board_member DROP FOREIGN KEY FK_DCFABEDFE7EC5785');
        $this->addSql('ALTER TABLE case_entity DROP FOREIGN KEY FK_A7C603C1EC7CEA0C');
        $this->addSql('DROP TABLE sub_board');
        $this->addSql('DROP INDEX IDX_DCFABEDFE7EC5785 ON board_member');
        $this->addSql('ALTER TABLE board_member DROP board_id');
        $this->addSql('DROP INDEX IDX_A7C603C1EC7CEA0C ON case_entity');
        $this->addSql('ALTER TABLE case_entity DROP subboard_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE sub_board (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', main_board_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', municipality_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_B4124FFE3ECE46F0 (main_board_id), INDEX IDX_B4124FFEAE6F181C (municipality_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE sub_board ADD CONSTRAINT FK_B4124FFE3ECE46F0 FOREIGN KEY (main_board_id) REFERENCES board (id)');
        $this->addSql('ALTER TABLE sub_board ADD CONSTRAINT FK_B4124FFEAE6F181C FOREIGN KEY (municipality_id) REFERENCES municipality (id)');
        $this->addSql('ALTER TABLE board_member ADD board_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE board_member ADD CONSTRAINT FK_DCFABEDFE7EC5785 FOREIGN KEY (board_id) REFERENCES sub_board (id)');
        $this->addSql('CREATE INDEX IDX_DCFABEDFE7EC5785 ON board_member (board_id)');
        $this->addSql('ALTER TABLE case_entity ADD subboard_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE case_entity ADD CONSTRAINT FK_A7C603C1EC7CEA0C FOREIGN KEY (subboard_id) REFERENCES sub_board (id)');
        $this->addSql('CREATE INDEX IDX_A7C603C1EC7CEA0C ON case_entity (subboard_id)');
    }
}
