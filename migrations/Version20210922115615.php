<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210922115615 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE agenda DROP FOREIGN KEY FK_2CEDC877E7EC5785');
        $this->addSql('DROP INDEX IDX_2CEDC877E7EC5785 ON agenda');
        $this->addSql('ALTER TABLE agenda ADD sub_board_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', DROP board_id');
        $this->addSql('ALTER TABLE agenda ADD CONSTRAINT FK_2CEDC8775D43B9DC FOREIGN KEY (sub_board_id) REFERENCES sub_board (id)');
        $this->addSql('CREATE INDEX IDX_2CEDC8775D43B9DC ON agenda (sub_board_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE agenda DROP FOREIGN KEY FK_2CEDC8775D43B9DC');
        $this->addSql('DROP INDEX IDX_2CEDC8775D43B9DC ON agenda');
        $this->addSql('ALTER TABLE agenda ADD board_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', DROP sub_board_id');
        $this->addSql('ALTER TABLE agenda ADD CONSTRAINT FK_2CEDC877E7EC5785 FOREIGN KEY (board_id) REFERENCES board (id)');
        $this->addSql('CREATE INDEX IDX_2CEDC877E7EC5785 ON agenda (board_id)');
    }
}
