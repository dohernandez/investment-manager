<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200219225528 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE projection_wallet_book_entry (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, type VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, total LONGTEXT NOT NULL COMMENT \'(DC2Type:object)\', INDEX IDX_A64F5820727ACA70 (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE projection_wallet_book_entry ADD CONSTRAINT FK_A64F5820727ACA70 FOREIGN KEY (parent_id) REFERENCES projection_wallet_book_entry (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE projection_wallet_book_entry DROP FOREIGN KEY FK_A64F5820727ACA70');
        $this->addSql('DROP TABLE projection_wallet_book_entry');
    }
}
