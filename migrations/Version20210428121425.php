<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210428121425 extends AbstractMigration
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
        $this->addSql('ALTER TABLE complaint_category ADD municipality_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE complaint_category ADD CONSTRAINT FK_1A553043AE6F181C FOREIGN KEY (municipality_id) REFERENCES municipality (id)');
        $this->addSql('CREATE INDEX IDX_1A553043AE6F181C ON complaint_category (municipality_id)');
        $this->addSql('ALTER TABLE party ADD municipality_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE party ADD CONSTRAINT FK_89954EE0AE6F181C FOREIGN KEY (municipality_id) REFERENCES municipality (id)');
        $this->addSql('CREATE INDEX IDX_89954EE0AE6F181C ON party (municipality_id)');
        $this->addSql('ALTER TABLE sub_board ADD municipality_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE sub_board ADD CONSTRAINT FK_B4124FFEAE6F181C FOREIGN KEY (municipality_id) REFERENCES municipality (id)');
        $this->addSql('CREATE INDEX IDX_B4124FFEAE6F181C ON sub_board (municipality_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE board_member DROP FOREIGN KEY FK_DCFABEDFAE6F181C');
        $this->addSql('DROP INDEX IDX_DCFABEDFAE6F181C ON board_member');
        $this->addSql('ALTER TABLE board_member DROP municipality_id');
        $this->addSql('ALTER TABLE complaint_category DROP FOREIGN KEY FK_1A553043AE6F181C');
        $this->addSql('DROP INDEX IDX_1A553043AE6F181C ON complaint_category');
        $this->addSql('ALTER TABLE complaint_category DROP municipality_id');
        $this->addSql('ALTER TABLE party DROP FOREIGN KEY FK_89954EE0AE6F181C');
        $this->addSql('DROP INDEX IDX_89954EE0AE6F181C ON party');
        $this->addSql('ALTER TABLE party DROP municipality_id');
        $this->addSql('ALTER TABLE sub_board DROP FOREIGN KEY FK_B4124FFEAE6F181C');
        $this->addSql('DROP INDEX IDX_B4124FFEAE6F181C ON sub_board');
        $this->addSql('ALTER TABLE sub_board DROP municipality_id');
    }
}
