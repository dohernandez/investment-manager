<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200412212216 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DELETE FROM event_source WHERE event_name = \'App\\\\Domain\\\\Market\\\\Event\\\StockPriceUpdated\'');
        $this->addSql('UPDATE projection_stock SET price_id = NULL');
        $this->addSql('DELETE FROM snapshot');
        $this->addSql('DELETE FROM projection_exchange_money_rate');

        $this->addSql('ALTER TABLE projection_stock DROP FOREIGN KEY FK_A7C9731AD614C7E7');
        $this->addSql('DROP TABLE projection_stock_price');
        $this->addSql('CREATE TABLE projection_report_weekly (id INT AUTO_INCREMENT NOT NULL, week VARCHAR(255) NOT NULL COMMENT \'(DC2Type:week)\', report JSON NOT NULL COMMENT \'(DC2Type:data)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', UNIQUE INDEX UNIQ_DEDA65CA5B5A69C0 (week), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE projection_market_price (id INT AUTO_INCREMENT NOT NULL, price JSON NOT NULL, change_price JSON NOT NULL, change_percentage DOUBLE PRECISION NOT NULL, pe_ratio DOUBLE PRECISION NOT NULL, pre_close JSON NOT NULL, week_52_low JSON NOT NULL, week_52_high JSON NOT NULL, updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE projection_market_data (id INT AUTO_INCREMENT NOT NULL, stock_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', price_id INT DEFAULT NULL, open JSON NOT NULL, close JSON DEFAULT NULL, day_low JSON NOT NULL, day_high JSON NOT NULL, week_low JSON DEFAULT NULL, week_high JSON DEFAULT NULL, currency VARCHAR(3) DEFAULT NULL, date_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', INDEX IDX_3F9AAB35DCD6110 (stock_id), INDEX IDX_3F9AAB35D614C7E7 (price_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE projection_market_data ADD CONSTRAINT FK_3F9AAB35DCD6110 FOREIGN KEY (stock_id) REFERENCES projection_stock (id)');
        $this->addSql('ALTER TABLE projection_market_data ADD CONSTRAINT FK_3F9AAB35D614C7E7 FOREIGN KEY (price_id) REFERENCES projection_market_price (id)');
        $this->addSql('ALTER TABLE projection_stock ADD historical_updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\'');
        $this->addSql('ALTER TABLE projection_stock ADD CONSTRAINT FK_A7C9731AD614C7E7 FOREIGN KEY (price_id) REFERENCES projection_market_price (id)');
        $this->addSql('DROP INDEX UNIQ_F17B75C4C40EDFCD ON projection_exchange_money_rate');
        $this->addSql('ALTER TABLE projection_exchange_money_rate ADD date_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\'');
        $this->addSql('CREATE UNIQUE INDEX paar_currency_date_at_idx ON projection_exchange_money_rate (paar_currency, date_at)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE projection_stock DROP FOREIGN KEY FK_A7C9731AD614C7E7');
        $this->addSql('ALTER TABLE projection_market_data DROP FOREIGN KEY FK_3F9AAB35D614C7E7');
        $this->addSql('CREATE TABLE projection_stock_price (id INT AUTO_INCREMENT NOT NULL, price LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:object)\', change_price LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:object)\', pe_ratio DOUBLE PRECISION NOT NULL, pre_close LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:object)\', open LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:object)\', day_low LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:object)\', day_high LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:object)\', week_52_low LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:object)\', week_52_high LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:object)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', change_percentage DOUBLE PRECISION NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('DROP TABLE projection_report_weekly');
        $this->addSql('DROP TABLE projection_market_price');
        $this->addSql('DROP TABLE projection_market_data');
        $this->addSql('DROP INDEX paar_currency_date_at_idx ON projection_exchange_money_rate');
        $this->addSql('ALTER TABLE projection_exchange_money_rate DROP date_at');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F17B75C4C40EDFCD ON projection_exchange_money_rate (paar_currency)');
        $this->addSql('ALTER TABLE projection_stock DROP historical_updated_at');
        $this->addSql('ALTER TABLE projection_stock ADD CONSTRAINT FK_A7C9731AD614C7E7 FOREIGN KEY (price_id) REFERENCES projection_stock_price (id)');
    }
}
