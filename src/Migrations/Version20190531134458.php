<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190531134458 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE trade (id INT AUTO_INCREMENT NOT NULL, stock_id INT NOT NULL, wallet_id INT NOT NULL, number INT NOT NULL, buy_amount NUMERIC(11, 2) DEFAULT NULL, buy_paid NUMERIC(11, 2) DEFAULT NULL, sell_amount NUMERIC(11, 2) DEFAULT NULL, sell_paid NUMERIC(11, 2) DEFAULT NULL, opened_at DATETIME NOT NULL, amount NUMERIC(11, 2) NOT NULL, capital NUMERIC(11, 2) NOT NULL, dividend NUMERIC(11, 2) DEFAULT NULL, closed_at DATETIME DEFAULT NULL, status VARCHAR(10) NOT NULL, net NUMERIC(11, 2) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_7E1A4366DCD6110 (stock_id), INDEX IDX_7E1A4366712520F3 (wallet_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE trade ADD CONSTRAINT FK_7E1A4366DCD6110 FOREIGN KEY (stock_id) REFERENCES stock (id)');
        $this->addSql('ALTER TABLE trade ADD CONSTRAINT FK_7E1A4366712520F3 FOREIGN KEY (wallet_id) REFERENCES wallet (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE trade');
    }
}
