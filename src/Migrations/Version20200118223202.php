<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200118223202 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE projection_stock_dividend (id INT AUTO_INCREMENT NOT NULL, stock_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', ex_date DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', payment_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\', record_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\', status VARCHAR(255) NOT NULL, value LONGTEXT NOT NULL COMMENT \'(DC2Type:object)\', change_from_prev DOUBLE PRECISION DEFAULT NULL, change_from_prev_year DOUBLE PRECISION DEFAULT NULL, prior_12_months_yield DOUBLE PRECISION DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', INDEX IDX_4560E9FBDCD6110 (stock_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE projection_stock_dividend ADD CONSTRAINT FK_4560E9FBDCD6110 FOREIGN KEY (stock_id) REFERENCES projection_stock (id)');
        $this->addSql('ALTER TABLE projection_stock ADD dividends_sync_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\'');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE projection_stock DROP dividends_sync_at');
        $this->addSql('DROP TABLE projection_stock_dividend');
    }
}
