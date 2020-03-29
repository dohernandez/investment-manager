<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191204151258 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE event_source (no BIGINT AUTO_INCREMENT NOT NULL, event_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', event_name VARCHAR(256) NOT NULL, payload LONGTEXT NOT NULL COMMENT \'(DC2Type:object)\', metadata LONGTEXT NOT NULL COMMENT \'(DC2Type:object)\', aggregate_type VARCHAR(256) NOT NULL, aggregate_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', aggregate_version SMALLINT NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX event_idx (event_id), PRIMARY KEY(no)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE event_source');
    }
}
