<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200217195222 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE projection_operation (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', wallet_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', position_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', stock LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\', stock_id VARCHAR(255) DEFAULT NULL, type VARCHAR(255) NOT NULL, amount INT DEFAULT NULL, price LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\', price_change LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\', price_change_commission LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\', value LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\', date_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', commission LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', INDEX IDX_191D31BF712520F3 (wallet_id), INDEX IDX_191D31BFDD842E46 (position_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE projection_position (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', book_id INT DEFAULT NULL, wallet_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', stock LONGTEXT NOT NULL COMMENT \'(DC2Type:object)\', stock_id VARCHAR(255) NOT NULL, amount INT NOT NULL, capital LONGTEXT NOT NULL COMMENT \'(DC2Type:object)\', invested LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\', status VARCHAR(255) NOT NULL, opened_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', closed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', UNIQUE INDEX UNIQ_DABB36F516A2B381 (book_id), INDEX IDX_DABB36F5712520F3 (wallet_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE projection_position_book (id INT AUTO_INCREMENT NOT NULL, currency LONGTEXT NOT NULL COMMENT \'(DC2Type:object)\', buy LONGTEXT NOT NULL COMMENT \'(DC2Type:object)\', sell LONGTEXT NOT NULL COMMENT \'(DC2Type:object)\', average_price LONGTEXT NOT NULL COMMENT \'(DC2Type:object)\', next_dividend LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\', next_dividend_yield DOUBLE PRECISION DEFAULT NULL, to_pay_dividend LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\', to_pay_dividend_yield DOUBLE PRECISION DEFAULT NULL, benefits LONGTEXT NOT NULL COMMENT \'(DC2Type:object)\', percentage_benefits DOUBLE PRECISION NOT NULL, changed LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\', percentage_changed DOUBLE PRECISION DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE projection_operation ADD CONSTRAINT FK_191D31BF712520F3 FOREIGN KEY (wallet_id) REFERENCES projection_wallet (id)');
        $this->addSql('ALTER TABLE projection_operation ADD CONSTRAINT FK_191D31BFDD842E46 FOREIGN KEY (position_id) REFERENCES projection_position (id)');
        $this->addSql('ALTER TABLE projection_position ADD CONSTRAINT FK_DABB36F516A2B381 FOREIGN KEY (book_id) REFERENCES projection_position_book (id)');
        $this->addSql('ALTER TABLE projection_position ADD CONSTRAINT FK_DABB36F5712520F3 FOREIGN KEY (wallet_id) REFERENCES projection_wallet (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE projection_operation DROP FOREIGN KEY FK_191D31BFDD842E46');
        $this->addSql('ALTER TABLE projection_position DROP FOREIGN KEY FK_DABB36F516A2B381');
        $this->addSql('DROP TABLE projection_operation');
        $this->addSql('DROP TABLE projection_position');
        $this->addSql('DROP TABLE projection_position_book');
    }
}
