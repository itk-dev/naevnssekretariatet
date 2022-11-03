<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221031135042 extends AbstractMigration
{
    private array $data = [];

    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE case_entity_complaint_category (case_entity_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', complaint_category_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_A62B6927AF060DA6 (case_entity_id), INDEX IDX_A62B6927D0DA653B (complaint_category_id), PRIMARY KEY(case_entity_id, complaint_category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE case_entity_complaint_category ADD CONSTRAINT FK_A62B6927AF060DA6 FOREIGN KEY (case_entity_id) REFERENCES case_entity (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE case_entity_complaint_category ADD CONSTRAINT FK_A62B6927D0DA653B FOREIGN KEY (complaint_category_id) REFERENCES complaint_category (id) ON DELETE CASCADE');

        // Migrate the existing cases.
        $this->addSql('INSERT INTO case_entity_complaint_category (case_entity_id, complaint_category_id) SELECT id, complaint_category_id FROM case_entity');

        $this->addSql('ALTER TABLE case_entity DROP FOREIGN KEY FK_A7C603C1D0DA653B');
        $this->addSql('DROP INDEX IDX_A7C603C1D0DA653B ON case_entity');
        $this->addSql('ALTER TABLE case_entity DROP complaint_category_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE case_entity ADD complaint_category_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');

        // Migrate just one of the complaint categories.
        $this->addSql('UPDATE case_entity SET complaint_category_id = (SELECT complaint_category_id FROM case_entity_complaint_category WHERE case_entity_id = case_entity.id LIMIT 1)');

        $this->addSql('ALTER TABLE case_entity ADD CONSTRAINT FK_A7C603C1D0DA653B FOREIGN KEY (complaint_category_id) REFERENCES complaint_category (id)');
        $this->addSql('CREATE INDEX IDX_A7C603C1D0DA653B ON case_entity (complaint_category_id)');
        $this->addSql('DROP TABLE case_entity_complaint_category');
    }
}
