<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200219230156 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE projection_wallet_book ADD commissions_id INT DEFAULT NULL, ADD dividends_id INT DEFAULT NULL, ADD connection_id INT DEFAULT NULL, ADD interest_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE projection_wallet_book ADD CONSTRAINT FK_7B12F7D2C23E429B FOREIGN KEY (commissions_id) REFERENCES projection_wallet_book_entry (id)');
        $this->addSql('ALTER TABLE projection_wallet_book ADD CONSTRAINT FK_7B12F7D243669A71 FOREIGN KEY (dividends_id) REFERENCES projection_wallet_book_entry (id)');
        $this->addSql('ALTER TABLE projection_wallet_book ADD CONSTRAINT FK_7B12F7D2DD03F01 FOREIGN KEY (connection_id) REFERENCES projection_wallet_book_entry (id)');
        $this->addSql('ALTER TABLE projection_wallet_book ADD CONSTRAINT FK_7B12F7D25A95FF89 FOREIGN KEY (interest_id) REFERENCES projection_wallet_book_entry (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7B12F7D2C23E429B ON projection_wallet_book (commissions_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7B12F7D243669A71 ON projection_wallet_book (dividends_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7B12F7D2DD03F01 ON projection_wallet_book (connection_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7B12F7D25A95FF89 ON projection_wallet_book (interest_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE projection_wallet_book DROP FOREIGN KEY FK_7B12F7D2C23E429B');
        $this->addSql('ALTER TABLE projection_wallet_book DROP FOREIGN KEY FK_7B12F7D243669A71');
        $this->addSql('ALTER TABLE projection_wallet_book DROP FOREIGN KEY FK_7B12F7D2DD03F01');
        $this->addSql('ALTER TABLE projection_wallet_book DROP FOREIGN KEY FK_7B12F7D25A95FF89');
        $this->addSql('DROP INDEX UNIQ_7B12F7D2C23E429B ON projection_wallet_book');
        $this->addSql('DROP INDEX UNIQ_7B12F7D243669A71 ON projection_wallet_book');
        $this->addSql('DROP INDEX UNIQ_7B12F7D2DD03F01 ON projection_wallet_book');
        $this->addSql('DROP INDEX UNIQ_7B12F7D25A95FF89 ON projection_wallet_book');
        $this->addSql('ALTER TABLE projection_wallet_book DROP commissions_id, DROP dividends_id, DROP connection_id, DROP interest_id');
    }
}
