<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230421074139 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Create HearingRecipient table
        $this->addSql('CREATE TABLE hearing_recipient (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', hearing_post_request_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', recipient_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', document_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_183E8E666DA919AD (hearing_post_request_id), INDEX IDX_183E8E66E92F8F78 (recipient_id), INDEX IDX_183E8E66C33F7837 (document_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE hearing_recipient ADD CONSTRAINT FK_183E8E666DA919AD FOREIGN KEY (hearing_post_request_id) REFERENCES hearing_post_request (id)');
        $this->addSql('ALTER TABLE hearing_recipient ADD CONSTRAINT FK_183E8E66E92F8F78 FOREIGN KEY (recipient_id) REFERENCES party (id)');
        $this->addSql('ALTER TABLE hearing_recipient ADD CONSTRAINT FK_183E8E66C33F7837 FOREIGN KEY (document_id) REFERENCES document (id)');

        // Migrate HearingPostRequest recipients to HearingRecipient
        // Generation of UuidV4 in sql see @https://emmer.dev/blog/generating-v4-uuids-in-mysql/
        $this->addSql("
            INSERT INTO hearing_recipient
            SELECT UNHEX(
                    CONCAT(
                        LPAD(HEX(FLOOR(RAND() * 0xffff)), 4, '0'),
                        LPAD(HEX(FLOOR(RAND() * 0xffff)), 4, '0'),
                        LPAD(HEX(FLOOR(RAND() * 0xffff)), 4, '0'),
                        CONCAT('4', LPAD(HEX(FLOOR(RAND() * 0x0fff)), 3, '0')),
                        CONCAT(HEX(FLOOR(RAND() * 4 + 8)), LPAD(HEX(FLOOR(RAND() * 0x0fff)), 3, '0')),
                        LPAD(HEX(FLOOR(RAND() * 0xffff)), 4, '0'),
                        LPAD(HEX(FLOOR(RAND() * 0xffff)), 4, '0'),
                        LPAD(HEX(FLOOR(RAND() * 0xffff)), 4, '0')
                    )
                   )                                                     AS id,
                   hearing_post_request.id                               AS hearing_post_request_id,
                   recipient_id                                          AS recipient_id,
                   hearing_post.document_id                              AS document_id,
                   hearing_post.created_at                               AS created_at,
                   hearing_post.updated_at                               AS updated_at
            FROM   hearing_post_request
                       JOIN hearing_post
                            ON hearing_post_request.id = hearing_post.id
        ");

        // Alter HearingPostResponse by adding reference to document
        $this->addSql('ALTER TABLE hearing_post_response ADD document_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE hearing_post_response ADD CONSTRAINT FK_5E5138CCC33F7837 FOREIGN KEY (document_id) REFERENCES document (id)');
        $this->addSql('CREATE INDEX IDX_5E5138CCC33F7837 ON hearing_post_response (document_id)');

        // Migrate HearingPost document id to HearingPostResponse
        $this->addSql('
            UPDATE hearing_post_response
                JOIN hearing_post
                    ON hearing_post_response.id = hearing_post.id
                SET    hearing_post_response.document_id = hearing_post.document_id
        ');

        // Clean up
        $this->addSql('ALTER TABLE hearing_post DROP FOREIGN KEY FK_54293ABFC33F7837');
        $this->addSql('DROP INDEX IDX_54293ABFC33F7837 ON hearing_post');
        $this->addSql('ALTER TABLE hearing_post DROP document_id');
        $this->addSql('ALTER TABLE hearing_post_request DROP FOREIGN KEY FK_A12A6D00E92F8F78');
        $this->addSql('DROP INDEX IDX_A12A6D00E92F8F78 ON hearing_post_request');
        $this->addSql('ALTER TABLE hearing_post_request DROP recipient_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE hearing_post ADD document_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE hearing_post ADD CONSTRAINT FK_54293ABFC33F7837 FOREIGN KEY (document_id) REFERENCES document (id)');
        $this->addSql('CREATE INDEX IDX_54293ABFC33F7837 ON hearing_post (document_id)');
        $this->addSql('ALTER TABLE hearing_post_request ADD recipient_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');

        // Migrating HearingRecipients to HearingPostRequest
        // HearingPostRequests can only contain one recipient so some data will be lost,
        // and some documents will not be cleaned up on the case/server.
        $this->addSql('
        UPDATE hearing_post_request
            JOIN hearing_recipient
                ON hearing_recipient.hearing_post_request_id = hearing_post_request.id
            JOIN hearing_post
                ON hearing_post_request.id = hearing_post.id
            SET    hearing_post_request.recipient_id = hearing_recipient.recipient_id,
                   hearing_post.document_id = hearing_recipient.document_id
        ');

        $this->addSql('ALTER TABLE hearing_post_request ADD CONSTRAINT FK_A12A6D00E92F8F78 FOREIGN KEY (recipient_id) REFERENCES party (id)');
        $this->addSql('CREATE INDEX IDX_A12A6D00E92F8F78 ON hearing_post_request (recipient_id)');

        // Move document from HearingPostResponse to HearingPost
        $this->addSql('
            UPDATE hearing_post
                JOIN hearing_post_response
                    ON hearing_post.id = hearing_post_response.id
                SET    hearing_post.document_id = hearing_post_response.document_id
        ');

        $this->addSql('ALTER TABLE hearing_post_response DROP FOREIGN KEY FK_5E5138CCC33F7837');
        $this->addSql('DROP INDEX IDX_5E5138CCC33F7837 ON hearing_post_response');
        $this->addSql('ALTER TABLE hearing_post_response DROP document_id');

        $this->addSql('DROP TABLE hearing_recipient');
    }
}
