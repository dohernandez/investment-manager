<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200109232211 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE projection_stock (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', market_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', type_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', sector_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', industry_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', name VARCHAR(255) NOT NULL, symbol VARCHAR(255) NOT NULL, value LONGTEXT NOT NULL COMMENT \'(DC2Type:object)\', description VARCHAR(255) DEFAULT NULL, notes LONGTEXT DEFAULT NULL, metadata LONGTEXT NOT NULL COMMENT \'(DC2Type:object)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', updated_price_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\', updated_dividend_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\', INDEX IDX_A7C9731A622F3F37 (market_id), INDEX IDX_A7C9731AC54C8C93 (type_id), INDEX IDX_A7C9731ADE95C867 (sector_id), INDEX IDX_A7C9731A2B19A734 (industry_id), UNIQUE INDEX stock_market_idx (symbol, market_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE projection_stock_info (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', name VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE projection_stock_market (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', name VARCHAR(255) NOT NULL, currency LONGTEXT NOT NULL COMMENT \'(DC2Type:object)\', country VARCHAR(2) NOT NULL, symbol VARCHAR(255) NOT NULL, metadata LONGTEXT NOT NULL COMMENT \'(DC2Type:object)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE projection_stock ADD CONSTRAINT FK_A7C9731A622F3F37 FOREIGN KEY (market_id) REFERENCES projection_stock_market (id)');
        $this->addSql('ALTER TABLE projection_stock ADD CONSTRAINT FK_A7C9731AC54C8C93 FOREIGN KEY (type_id) REFERENCES projection_stock_info (id)');
        $this->addSql('ALTER TABLE projection_stock ADD CONSTRAINT FK_A7C9731ADE95C867 FOREIGN KEY (sector_id) REFERENCES projection_stock_info (id)');
        $this->addSql('ALTER TABLE projection_stock ADD CONSTRAINT FK_A7C9731A2B19A734 FOREIGN KEY (industry_id) REFERENCES projection_stock_info (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE projection_stock DROP FOREIGN KEY FK_A7C9731AC54C8C93');
        $this->addSql('ALTER TABLE projection_stock DROP FOREIGN KEY FK_A7C9731ADE95C867');
        $this->addSql('ALTER TABLE projection_stock DROP FOREIGN KEY FK_A7C9731A2B19A734');
        $this->addSql('ALTER TABLE projection_stock DROP FOREIGN KEY FK_A7C9731A622F3F37');
        $this->addSql('DROP TABLE projection_stock');
        $this->addSql('DROP TABLE projection_stock_info');
        $this->addSql('DROP TABLE projection_stock_market');
    }
}
