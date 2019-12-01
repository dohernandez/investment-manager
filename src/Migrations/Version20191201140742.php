<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191201140742 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE manager_event (no BIGINT AUTO_INCREMENT NOT NULL, event_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', event_name VARCHAR(100) NOT NULL, payload JSON NOT NULL, metadata JSON NOT NULL, aggregate_type VARCHAR(50) NOT NULL, aggregate_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', aggregate_version SMALLINT NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX event_idx (event_id), PRIMARY KEY(no)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE manager_event');
    }
}
