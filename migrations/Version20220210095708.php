<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220210095708 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE hearing_post DROP FOREIGN KEY FK_54293ABFF624B39D');
        $this->addSql('DROP INDEX IDX_54293ABFF624B39D ON hearing_post');
        $this->addSql('ALTER TABLE hearing_post DROP sender_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE hearing_post ADD sender_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE hearing_post ADD CONSTRAINT FK_54293ABFF624B39D FOREIGN KEY (sender_id) REFERENCES party (id)');
        $this->addSql('CREATE INDEX IDX_54293ABFF624B39D ON hearing_post (sender_id)');
    }
}
