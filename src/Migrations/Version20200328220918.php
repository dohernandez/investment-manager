<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200328220918 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE projection_wallet_book ADD dividends_projection_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE projection_wallet_book ADD CONSTRAINT FK_7B12F7D2F940BE21 FOREIGN KEY (dividends_projection_id) REFERENCES projection_wallet_book_entry (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7B12F7D2F940BE21 ON projection_wallet_book (dividends_projection_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE blocked_zip_codes (id_blocked_zip_codes INT UNSIGNED AUTO_INCREMENT NOT NULL, zip_code VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_general_ci`, week CHAR(8) CHARACTER SET utf8 NOT NULL COLLATE `utf8_general_ci`, region_id VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_general_ci`, created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetime)\', updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetime)\', INDEX zip_code_idx (zip_code), INDEX idx_region_id (region_id), INDEX week_idx (week), PRIMARY KEY(id_blocked_zip_codes)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE projection_wallet_book DROP FOREIGN KEY FK_7B12F7D2F940BE21');
        $this->addSql('DROP INDEX UNIQ_7B12F7D2F940BE21 ON projection_wallet_book');
        $this->addSql('ALTER TABLE projection_wallet_book DROP dividends_projection_id');
    }
}
