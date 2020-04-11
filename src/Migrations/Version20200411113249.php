<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200411113249 extends AbstractMigration
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
        $this->addSql('UPDATE projection_stock ps SET ps.price_id = NULL WHERE ps.price_id IS NOT NULL');
        $this->addSql('DELETE FROM snapshot;
');

        $this->addSql('CREATE TABLE projection_market_price (id INT AUTO_INCREMENT NOT NULL, data_id INT DEFAULT NULL, price JSON NOT NULL, change_price JSON NOT NULL, change_percentage DOUBLE PRECISION NOT NULL, pe_ratio DOUBLE PRECISION NOT NULL, pre_close JSON NOT NULL, week_52_low JSON NOT NULL, week_52_high JSON NOT NULL, updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', UNIQUE INDEX UNIQ_4852BF4037F5A13C (data_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE projection_market_data (id INT AUTO_INCREMENT NOT NULL, stock_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', open JSON NOT NULL, close JSON DEFAULT NULL, day_low JSON NOT NULL, day_high JSON NOT NULL, week_low JSON DEFAULT NULL, week_high JSON DEFAULT NULL, date_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', INDEX IDX_3F9AAB35DCD6110 (stock_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE projection_market_price ADD CONSTRAINT FK_4852BF4037F5A13C FOREIGN KEY (data_id) REFERENCES projection_market_data (id)');
        $this->addSql('ALTER TABLE projection_market_data ADD CONSTRAINT FK_3F9AAB35DCD6110 FOREIGN KEY (stock_id) REFERENCES projection_stock (id)');
        $this->addSql('ALTER TABLE projection_stock DROP FOREIGN KEY FK_A7C9731AD614C7E7');
        $this->addSql('DROP TABLE projection_stock_price');
        $this->addSql('ALTER TABLE projection_stock ADD historical_updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\'');
        $this->addSql('ALTER TABLE projection_stock ADD CONSTRAINT FK_A7C9731AD614C7E7 FOREIGN KEY (price_id) REFERENCES projection_market_price (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE projection_stock DROP FOREIGN KEY FK_A7C9731AD614C7E7');
        $this->addSql('ALTER TABLE projection_market_price DROP FOREIGN KEY FK_4852BF4037F5A13C');
        $this->addSql('CREATE TABLE projection_stock_price (id INT AUTO_INCREMENT NOT NULL, price LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:object)\', change_price LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:object)\', pe_ratio DOUBLE PRECISION NOT NULL, pre_close LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:object)\', open LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:object)\', day_low LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:object)\', day_high LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:object)\', week_52_low LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:object)\', week_52_high LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:object)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', change_percentage DOUBLE PRECISION NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('DROP TABLE projection_market_price');
        $this->addSql('DROP TABLE projection_market_data');
        $this->addSql('ALTER TABLE projection_stock DROP historical_updated_at');
        $this->addSql('ALTER TABLE projection_stock ADD CONSTRAINT FK_A7C9731AD614C7E7 FOREIGN KEY (price_id) REFERENCES projection_stock_price (id)');
    }
}
