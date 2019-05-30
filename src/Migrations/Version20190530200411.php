<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190530200411 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE broker_stock (broker_id INT NOT NULL, stock_id INT NOT NULL, INDEX IDX_7418EBCC6CC064FC (broker_id), INDEX IDX_7418EBCCDCD6110 (stock_id), PRIMARY KEY(broker_id, stock_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE broker_stock ADD CONSTRAINT FK_7418EBCC6CC064FC FOREIGN KEY (broker_id) REFERENCES broker (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE broker_stock ADD CONSTRAINT FK_7418EBCCDCD6110 FOREIGN KEY (stock_id) REFERENCES stock (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE broker_stock');
    }
}
