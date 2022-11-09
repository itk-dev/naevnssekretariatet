<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220316134557 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE complaint_category_board (complaint_category_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', board_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_17E1DEA9D0DA653B (complaint_category_id), INDEX IDX_17E1DEA9E7EC5785 (board_id), PRIMARY KEY(complaint_category_id, board_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE complaint_category_board ADD CONSTRAINT FK_17E1DEA9D0DA653B FOREIGN KEY (complaint_category_id) REFERENCES complaint_category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE complaint_category_board ADD CONSTRAINT FK_17E1DEA9E7EC5785 FOREIGN KEY (board_id) REFERENCES board (id) ON DELETE CASCADE');

        // Insert ManyToMany relation between complaint category and board
        $this->addSql('INSERT INTO complaint_category_board (complaint_category_id, board_id) SELECT id, board_id  from complaint_category');

        $this->addSql('ALTER TABLE complaint_category DROP FOREIGN KEY FK_1A553043E7EC5785');
        $this->addSql('DROP INDEX IDX_1A553043E7EC5785 ON complaint_category');
        $this->addSql('ALTER TABLE complaint_category DROP board_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE complaint_category_board');
        $this->addSql('ALTER TABLE complaint_category ADD board_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE complaint_category ADD CONSTRAINT FK_1A553043E7EC5785 FOREIGN KEY (board_id) REFERENCES board (id)');
        $this->addSql('CREATE INDEX IDX_1A553043E7EC5785 ON complaint_category (board_id)');
    }
}
