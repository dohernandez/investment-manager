<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190602214738 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE operation (id INT AUTO_INCREMENT NOT NULL, stock_id INT NOT NULL, trade_id INT NOT NULL, date_at DATETIME NOT NULL, type VARCHAR(30) NOT NULL, price NUMERIC(11, 2) NOT NULL, price_change NUMERIC(11, 4) DEFAULT NULL, price_change_commission NUMERIC(11, 4) DEFAULT NULL, value NUMERIC(11, 2) NOT NULL, commission NUMERIC(11, 4) DEFAULT NULL, amount INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_1981A66DDCD6110 (stock_id), INDEX IDX_1981A66DC2D9760 (trade_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE operation ADD CONSTRAINT FK_1981A66DDCD6110 FOREIGN KEY (stock_id) REFERENCES stock (id)');
        $this->addSql('ALTER TABLE operation ADD CONSTRAINT FK_1981A66DC2D9760 FOREIGN KEY (trade_id) REFERENCES trade (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE operation');
    }
}
