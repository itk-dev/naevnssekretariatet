<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230427123438 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE board DROP FOREIGN KEY FK_58562B47DC5A710B');
        $this->addSql('ALTER TABLE board DROP FOREIGN KEY FK_58562B477026F51C');
        $this->addSql('ALTER TABLE board DROP FOREIGN KEY FK_58562B47B9A904B5');
        $this->addSql('DROP INDEX IDX_58562B47DC5A710B ON board');
        $this->addSql('DROP INDEX IDX_58562B47B9A904B5 ON board');
        $this->addSql('DROP INDEX IDX_58562B477026F51C ON board');
        $this->addSql('ALTER TABLE board DROP receipt_case_id, DROP receipt_hearing_post_id, DROP hearing_post_response_template_id');
        $this->addSql('ALTER TABLE hearing_post_response DROP send_receipt');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE board ADD receipt_case_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', ADD receipt_hearing_post_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', ADD hearing_post_response_template_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE board ADD CONSTRAINT FK_58562B47DC5A710B FOREIGN KEY (receipt_case_id) REFERENCES mail_template (id)');
        $this->addSql('ALTER TABLE board ADD CONSTRAINT FK_58562B477026F51C FOREIGN KEY (hearing_post_response_template_id) REFERENCES mail_template (id)');
        $this->addSql('ALTER TABLE board ADD CONSTRAINT FK_58562B47B9A904B5 FOREIGN KEY (receipt_hearing_post_id) REFERENCES mail_template (id)');
        $this->addSql('CREATE INDEX IDX_58562B47DC5A710B ON board (receipt_case_id)');
        $this->addSql('CREATE INDEX IDX_58562B47B9A904B5 ON board (receipt_hearing_post_id)');
        $this->addSql('CREATE INDEX IDX_58562B477026F51C ON board (hearing_post_response_template_id)');
        $this->addSql('ALTER TABLE hearing_post_response ADD send_receipt TINYINT(1) DEFAULT 1 NOT NULL');
    }
}
