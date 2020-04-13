<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200414104931 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('UPDATE projection_stock_market SET price_id = NULL');

        $this->addSql('ALTER TABLE projection_market_price CHANGE price price JSON NOT NULL, CHANGE change_price change_price JSON NOT NULL, CHANGE pre_close pre_close JSON NOT NULL, CHANGE week_52_low week_52_low JSON NOT NULL, CHANGE week_52_high week_52_high JSON NOT NULL');
        $this->addSql('ALTER TABLE projection_market_data CHANGE open open JSON NOT NULL, CHANGE close close JSON DEFAULT NULL, CHANGE day_low day_low JSON NOT NULL, CHANGE day_high day_high JSON NOT NULL, CHANGE week_low week_low JSON DEFAULT NULL, CHANGE week_high week_high JSON DEFAULT NULL, CHANGE currency currency VARCHAR(3) DEFAULT NULL');
        $this->addSql('ALTER TABLE projection_stock_market DROP FOREIGN KEY FK_E83E75B1D614C7E7');
        $this->addSql('ALTER TABLE projection_stock_market ADD CONSTRAINT FK_E83E75B1D614C7E7 FOREIGN KEY (price_id) REFERENCES projection_market_price (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE projection_market_data CHANGE open open JSON NOT NULL, CHANGE close close JSON DEFAULT NULL, CHANGE day_low day_low JSON NOT NULL, CHANGE day_high day_high JSON NOT NULL, CHANGE week_low week_low JSON DEFAULT NULL, CHANGE week_high week_high JSON DEFAULT NULL, CHANGE currency currency VARCHAR(3) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE projection_market_price CHANGE price price JSON NOT NULL, CHANGE change_price change_price JSON NOT NULL, CHANGE pre_close pre_close JSON NOT NULL, CHANGE week_52_low week_52_low JSON NOT NULL, CHANGE week_52_high week_52_high JSON NOT NULL');
        $this->addSql('ALTER TABLE projection_stock_market DROP FOREIGN KEY FK_E83E75B1D614C7E7');
        $this->addSql('ALTER TABLE projection_stock_market ADD CONSTRAINT FK_E83E75B1D614C7E7 FOREIGN KEY (price_id) REFERENCES projection_stock_market_price (id)');
    }
}
