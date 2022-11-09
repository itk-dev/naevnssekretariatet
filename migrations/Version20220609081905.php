<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220609081905 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE board ADD receipt_case_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', ADD receipt_hearing_post_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE board ADD CONSTRAINT FK_58562B47DC5A710B FOREIGN KEY (receipt_case_id) REFERENCES mail_template (id)');
        $this->addSql('ALTER TABLE board ADD CONSTRAINT FK_58562B47B9A904B5 FOREIGN KEY (receipt_hearing_post_id) REFERENCES mail_template (id)');
        $this->addSql('CREATE INDEX IDX_58562B47DC5A710B ON board (receipt_case_id)');
        $this->addSql('CREATE INDEX IDX_58562B47B9A904B5 ON board (receipt_hearing_post_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE board DROP FOREIGN KEY FK_58562B47DC5A710B');
        $this->addSql('ALTER TABLE board DROP FOREIGN KEY FK_58562B47B9A904B5');
        $this->addSql('DROP INDEX IDX_58562B47DC5A710B ON board');
        $this->addSql('DROP INDEX IDX_58562B47B9A904B5 ON board');
        $this->addSql('ALTER TABLE board DROP receipt_case_id, DROP receipt_hearing_post_id');
    }
}
