<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220228094111 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE hearing_post_request (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE hearing_post_response (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE hearing_post_request ADD CONSTRAINT FK_A12A6D00BF396750 FOREIGN KEY (id) REFERENCES hearing_post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE hearing_post_response ADD CONSTRAINT FK_5E5138CCBF396750 FOREIGN KEY (id) REFERENCES hearing_post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE hearing_post ADD discr VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE hearing_post_request');
        $this->addSql('DROP TABLE hearing_post_response');
        $this->addSql('ALTER TABLE hearing_post DROP discr');
    }
}
