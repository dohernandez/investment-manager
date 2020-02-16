<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200229221939 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE projection_position_book ADD dividend_paid_id INT DEFAULT NULL, ADD dividend_retention_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE projection_position_book ADD CONSTRAINT FK_7188971E709AFD0E FOREIGN KEY (dividend_paid_id) REFERENCES projection_wallet_book_entry (id)');
        $this->addSql('ALTER TABLE projection_position_book ADD CONSTRAINT FK_7188971EE107F16D FOREIGN KEY (dividend_retention_id) REFERENCES projection_wallet_book_entry (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7188971E709AFD0E ON projection_position_book (dividend_paid_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7188971EE107F16D ON projection_position_book (dividend_retention_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE projection_position_book DROP FOREIGN KEY FK_7188971E709AFD0E');
        $this->addSql('ALTER TABLE projection_position_book DROP FOREIGN KEY FK_7188971EE107F16D');
        $this->addSql('DROP INDEX UNIQ_7188971E709AFD0E ON projection_position_book');
        $this->addSql('DROP INDEX UNIQ_7188971EE107F16D ON projection_position_book');
        $this->addSql('ALTER TABLE projection_position_book DROP dividend_paid_id, DROP dividend_retention_id');
    }
}
