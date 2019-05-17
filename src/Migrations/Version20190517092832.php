<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190517092832 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE stock_dividend (id INT AUTO_INCREMENT NOT NULL, stock_id INT NOT NULL, ex_date DATETIME NOT NULL, payment_date DATETIME DEFAULT NULL, record_date DATETIME DEFAULT NULL, status VARCHAR(20) NOT NULL, value NUMERIC(7, 4) NOT NULL, change_from_prev NUMERIC(7, 2) DEFAULT NULL, change_from_prev_year NUMERIC(7, 2) DEFAULT NULL, prior12months_yield NUMERIC(7, 2) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_62CB5D4BDCD6110 (stock_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE stock_dividend ADD CONSTRAINT FK_62CB5D4BDCD6110 FOREIGN KEY (stock_id) REFERENCES stock (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE stock_dividend');
    }
}
