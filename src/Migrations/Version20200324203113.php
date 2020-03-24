<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200324203113 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE projection_stock_market_price (id INT AUTO_INCREMENT NOT NULL, price LONGTEXT NOT NULL COMMENT \'(DC2Type:object)\', pre_close LONGTEXT NOT NULL COMMENT \'(DC2Type:object)\', open LONGTEXT NOT NULL COMMENT \'(DC2Type:object)\', day_low LONGTEXT NOT NULL COMMENT \'(DC2Type:object)\', day_high LONGTEXT NOT NULL COMMENT \'(DC2Type:object)\', week_52_low LONGTEXT NOT NULL COMMENT \'(DC2Type:object)\', week_52_high LONGTEXT NOT NULL COMMENT \'(DC2Type:object)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE projection_stock_market ADD price_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE projection_stock_market ADD CONSTRAINT FK_E83E75B1D614C7E7 FOREIGN KEY (price_id) REFERENCES projection_stock_market_price (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E83E75B1D614C7E7 ON projection_stock_market (price_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE projection_stock_market DROP FOREIGN KEY FK_E83E75B1D614C7E7');
        $this->addSql('DROP TABLE projection_stock_market_price');
        $this->addSql('DROP INDEX UNIQ_E83E75B1D614C7E7 ON projection_stock_market');
        $this->addSql('ALTER TABLE projection_stock_market DROP price_id');
    }
}
