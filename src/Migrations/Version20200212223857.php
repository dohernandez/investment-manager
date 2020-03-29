<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200212223857 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE projection_wallet_book (id INT AUTO_INCREMENT NOT NULL, invested LONGTEXT NOT NULL COMMENT \'(DC2Type:object)\', capital LONGTEXT NOT NULL COMMENT \'(DC2Type:object)\', funds LONGTEXT NOT NULL COMMENT \'(DC2Type:object)\', benefits LONGTEXT NOT NULL COMMENT \'(DC2Type:object)\', currency LONGTEXT NOT NULL COMMENT \'(DC2Type:object)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE projection_wallet (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', book_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, broker LONGTEXT NOT NULL COMMENT \'(DC2Type:object)\', account LONGTEXT NOT NULL COMMENT \'(DC2Type:object)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', account_id VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_CC54F518989D9B62 (slug), UNIQUE INDEX UNIQ_CC54F51816A2B381 (book_id), UNIQUE INDEX name_idx (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE projection_wallet ADD CONSTRAINT FK_CC54F51816A2B381 FOREIGN KEY (book_id) REFERENCES projection_wallet_book (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE projection_wallet DROP FOREIGN KEY FK_CC54F51816A2B381');
        $this->addSql('DROP TABLE projection_wallet_book');
        $this->addSql('DROP TABLE projection_wallet');
    }
}
