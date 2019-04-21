<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190419204257 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE account ADD alias VARCHAR(255) NOT NULL, CHANGE iban account_no VARCHAR(255) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7D3656A45E237E06 ON account (name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7D3656A443F810F5 ON account (account_no)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7D3656A4E16C6B94 ON account (alias)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX UNIQ_7D3656A45E237E06 ON account');
        $this->addSql('DROP INDEX UNIQ_7D3656A443F810F5 ON account');
        $this->addSql('DROP INDEX UNIQ_7D3656A4E16C6B94 ON account');
        $this->addSql('ALTER TABLE account ADD iban VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, DROP account_no, DROP alias');
    }
}
