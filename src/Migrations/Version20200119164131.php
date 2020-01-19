<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200119164131 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE projection_stock ADD next_dividend_id INT DEFAULT NULL, ADD to_pay_dividend_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE projection_stock ADD CONSTRAINT FK_A7C9731AC4313D87 FOREIGN KEY (next_dividend_id) REFERENCES projection_stock_dividend (id)');
        $this->addSql('ALTER TABLE projection_stock ADD CONSTRAINT FK_A7C9731A9F6BC8EE FOREIGN KEY (to_pay_dividend_id) REFERENCES projection_stock_dividend (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A7C9731AC4313D87 ON projection_stock (next_dividend_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A7C9731A9F6BC8EE ON projection_stock (to_pay_dividend_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE projection_stock DROP FOREIGN KEY FK_A7C9731AC4313D87');
        $this->addSql('ALTER TABLE projection_stock DROP FOREIGN KEY FK_A7C9731A9F6BC8EE');
        $this->addSql('DROP INDEX UNIQ_A7C9731AC4313D87 ON projection_stock');
        $this->addSql('DROP INDEX UNIQ_A7C9731A9F6BC8EE ON projection_stock');
        $this->addSql('ALTER TABLE projection_stock DROP next_dividend_id, DROP to_pay_dividend_id');
    }
}
