<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210511100140 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE case_entity ADD subboard_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE case_entity ADD CONSTRAINT FK_A7C603C1EC7CEA0C FOREIGN KEY (subboard_id) REFERENCES sub_board (id)');
        $this->addSql('CREATE INDEX IDX_A7C603C1EC7CEA0C ON case_entity (subboard_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE case_entity DROP FOREIGN KEY FK_A7C603C1EC7CEA0C');
        $this->addSql('DROP INDEX IDX_A7C603C1EC7CEA0C ON case_entity');
        $this->addSql('ALTER TABLE case_entity DROP subboard_id');
    }
}
