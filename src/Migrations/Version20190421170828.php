<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190421170828 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE transfer (id INT AUTO_INCREMENT NOT NULL, beneficiary_party_id INT NOT NULL, debtor_party_id INT NOT NULL, amount NUMERIC(5, 2) NOT NULL, date DATETIME NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_4034A3C017E283FA (beneficiary_party_id), INDEX IDX_4034A3C086696171 (debtor_party_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE transfer ADD CONSTRAINT FK_4034A3C017E283FA FOREIGN KEY (beneficiary_party_id) REFERENCES account (id)');
        $this->addSql('ALTER TABLE transfer ADD CONSTRAINT FK_4034A3C086696171 FOREIGN KEY (debtor_party_id) REFERENCES account (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE transfer');
    }
}
