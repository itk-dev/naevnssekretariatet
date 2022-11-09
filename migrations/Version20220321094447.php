<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220321094447 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE case_entity ADD created_by_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', ADD updated_by_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE case_entity ADD CONSTRAINT FK_A7C603C1B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE case_entity ADD CONSTRAINT FK_A7C603C1896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_A7C603C1B03A8386 ON case_entity (created_by_id)');
        $this->addSql('CREATE INDEX IDX_A7C603C1896DBBDE ON case_entity (updated_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE case_entity DROP FOREIGN KEY FK_A7C603C1B03A8386');
        $this->addSql('ALTER TABLE case_entity DROP FOREIGN KEY FK_A7C603C1896DBBDE');
        $this->addSql('DROP INDEX IDX_A7C603C1B03A8386 ON case_entity');
        $this->addSql('DROP INDEX IDX_A7C603C1896DBBDE ON case_entity');
        $this->addSql('ALTER TABLE case_entity DROP created_by_id, DROP updated_by_id');
    }
}
