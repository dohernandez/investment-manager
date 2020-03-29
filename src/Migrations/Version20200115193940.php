<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200115193940 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE projection_stock_price (id INT AUTO_INCREMENT NOT NULL, price LONGTEXT NOT NULL COMMENT \'(DC2Type:object)\', change_price LONGTEXT NOT NULL COMMENT \'(DC2Type:object)\', pe_ratio DOUBLE PRECISION NOT NULL, pre_close LONGTEXT NOT NULL COMMENT \'(DC2Type:object)\', open LONGTEXT NOT NULL COMMENT \'(DC2Type:object)\', day_low LONGTEXT NOT NULL COMMENT \'(DC2Type:object)\', day_high LONGTEXT NOT NULL COMMENT \'(DC2Type:object)\', week_52_low LONGTEXT NOT NULL COMMENT \'(DC2Type:object)\', week_52_high LONGTEXT NOT NULL COMMENT \'(DC2Type:object)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE projection_stock ADD price_id INT DEFAULT NULL, DROP updated_price_at, DROP updated_dividend_at');
        $this->addSql('ALTER TABLE projection_stock ADD CONSTRAINT FK_A7C9731AD614C7E7 FOREIGN KEY (price_id) REFERENCES projection_stock_price (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A7C9731AD614C7E7 ON projection_stock (price_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE projection_stock DROP FOREIGN KEY FK_A7C9731AD614C7E7');
        $this->addSql('DROP TABLE projection_stock_price');
        $this->addSql('DROP INDEX UNIQ_A7C9731AD614C7E7 ON projection_stock');
        $this->addSql('ALTER TABLE projection_stock ADD updated_price_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\', ADD updated_dividend_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\', DROP price_id');
    }
}
