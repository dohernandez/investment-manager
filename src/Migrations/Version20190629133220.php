<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190629133220 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('UPDATE stock SET value = null, last_change_price = null, pre_close = null, open = null, day_low = null, day_high = null, week_52_low  = null, week_52_high  = null');
        $this->addSql('ALTER TABLE stock CHANGE value value JSON DEFAULT NULL COMMENT \'(DC2Type:json)\', CHANGE last_change_price last_change_price JSON DEFAULT NULL COMMENT \'(DC2Type:json)\', CHANGE pre_close pre_close JSON DEFAULT NULL COMMENT \'(DC2Type:json)\', CHANGE open open JSON DEFAULT NULL COMMENT \'(DC2Type:json)\', CHANGE day_low day_low JSON DEFAULT NULL COMMENT \'(DC2Type:json)\', CHANGE day_high day_high JSON DEFAULT NULL COMMENT \'(DC2Type:json)\', CHANGE week_52_low week_52_low JSON DEFAULT NULL COMMENT \'(DC2Type:json)\', CHANGE week_52_high week_52_high JSON DEFAULT NULL COMMENT \'(DC2Type:json)\'');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE stock CHANGE value value JSON DEFAULT NULL COMMENT \'(DC2Type:json)\', CHANGE last_change_price last_change_price NUMERIC(10, 2) DEFAULT NULL, CHANGE pre_close pre_close NUMERIC(10, 3) DEFAULT NULL, CHANGE open open NUMERIC(10, 3) DEFAULT NULL, CHANGE day_low day_low NUMERIC(10, 3) DEFAULT NULL, CHANGE day_high day_high NUMERIC(10, 3) DEFAULT NULL, CHANGE week_52_low week_52_low NUMERIC(10, 3) DEFAULT NULL, CHANGE week_52_high week_52_high NUMERIC(10, 3) DEFAULT NULL');
    }
}
