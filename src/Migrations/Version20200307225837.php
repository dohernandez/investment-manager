<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200307225837 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE projection_position_book ADD next_dividend_after_taxes LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\', ADD next_dividend_yield_after_taxes DOUBLE PRECISION DEFAULT NULL, ADD to_pay_dividend_after_taxes LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\', ADD to_pay_dividend_yield_after_taxes DOUBLE PRECISION DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE projection_position_book DROP next_dividend_after_taxes, DROP next_dividend_yield_after_taxes, DROP to_pay_dividend_after_taxes, DROP to_pay_dividend_yield_after_taxes');
    }
}
