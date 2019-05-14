<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190514210745 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE stock_info (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(180) NOT NULL, type VARCHAR(180) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE stock ADD type_id INT DEFAULT NULL, ADD sector_id INT DEFAULT NULL, ADD industry_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE stock ADD CONSTRAINT FK_4B365660C54C8C93 FOREIGN KEY (type_id) REFERENCES stock_info (id)');
        $this->addSql('ALTER TABLE stock ADD CONSTRAINT FK_4B365660DE95C867 FOREIGN KEY (sector_id) REFERENCES stock_info (id)');
        $this->addSql('ALTER TABLE stock ADD CONSTRAINT FK_4B3656602B19A734 FOREIGN KEY (industry_id) REFERENCES stock_info (id)');
        $this->addSql('CREATE INDEX IDX_4B365660C54C8C93 ON stock (type_id)');
        $this->addSql('CREATE INDEX IDX_4B365660DE95C867 ON stock (sector_id)');
        $this->addSql('CREATE INDEX IDX_4B3656602B19A734 ON stock (industry_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE stock DROP FOREIGN KEY FK_4B365660C54C8C93');
        $this->addSql('ALTER TABLE stock DROP FOREIGN KEY FK_4B365660DE95C867');
        $this->addSql('ALTER TABLE stock DROP FOREIGN KEY FK_4B3656602B19A734');
        $this->addSql('DROP TABLE stock_info');
        $this->addSql('DROP INDEX IDX_4B365660C54C8C93 ON stock');
        $this->addSql('DROP INDEX IDX_4B365660DE95C867 ON stock');
        $this->addSql('DROP INDEX IDX_4B3656602B19A734 ON stock');
        $this->addSql('ALTER TABLE stock DROP type_id, DROP sector_id, DROP industry_id');
    }
}
