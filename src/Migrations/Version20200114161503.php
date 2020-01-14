<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200114161503 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE event_source CHANGE event_name event_name VARCHAR(256) NOT NULL, CHANGE aggregate_type aggregate_type VARCHAR(256) NOT NULL');
        $this->addSql('ALTER TABLE projection_stock CHANGE notes notes LONGTEXT NOT NULL, CHANGE updated_price_at updated_price_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', CHANGE updated_dividend_at updated_dividend_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\'');
        $this->addSql('ALTER TABLE projection_stock_market ADD yahoo_symbol VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE event_source CHANGE event_name event_name VARCHAR(256) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE aggregate_type aggregate_type VARCHAR(256) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE projection_stock CHANGE notes notes LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE updated_price_at updated_price_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\', CHANGE updated_dividend_at updated_dividend_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\'');
        $this->addSql('ALTER TABLE projection_stock_market DROP yahoo_symbol');
    }
}
