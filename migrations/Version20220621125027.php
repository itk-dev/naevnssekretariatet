<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\Uid\Uuid;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220621125027 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Generate Uuid for OS2Forms user
        $id = Uuid::v4()->toBinary();
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('INSERT INTO user (id, email, roles, name) VALUES (:id, "OS2Forms@example.com", \'["ROLE_ADMIN"]\', "OS2Forms")', ['id' => $id]);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM user WHERE name="OS2Forms"');
    }
}
