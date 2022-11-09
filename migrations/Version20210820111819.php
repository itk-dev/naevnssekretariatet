<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210820111819 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE case_parties (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', case_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', party_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', type VARCHAR(255) NOT NULL, INDEX IDX_ECC0ED0ECF10D4F5 (case_id), INDEX IDX_ECC0ED0E213C1059 (party_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE case_parties ADD CONSTRAINT FK_ECC0ED0ECF10D4F5 FOREIGN KEY (case_id) REFERENCES case_entity (id)');
        $this->addSql('ALTER TABLE case_parties ADD CONSTRAINT FK_ECC0ED0E213C1059 FOREIGN KEY (party_id) REFERENCES party (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE case_parties');
    }
}
