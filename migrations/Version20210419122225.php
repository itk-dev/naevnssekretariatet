<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210419122225 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD favorite_municipality_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6492B06433D FOREIGN KEY (favorite_municipality_id) REFERENCES municipality (id)');
        $this->addSql('CREATE INDEX IDX_8D93D6492B06433D ON user (favorite_municipality_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6492B06433D');
        $this->addSql('DROP INDEX IDX_8D93D6492B06433D ON user');
        $this->addSql('ALTER TABLE user DROP favorite_municipality_id');
    }
}
