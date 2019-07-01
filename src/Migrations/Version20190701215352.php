<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190701215352 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE account CHANGE withdraw withdraw JSON DEFAULT NULL COMMENT \'(DC2Type:json)\', CHANGE deposit deposit JSON DEFAULT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE wallet CHANGE invested invested JSON NOT NULL COMMENT \'(DC2Type:json)\', CHANGE capital capital JSON NOT NULL COMMENT \'(DC2Type:json)\', CHANGE funds funds JSON NOT NULL COMMENT \'(DC2Type:json)\', CHANGE dividend dividend JSON NOT NULL COMMENT \'(DC2Type:json)\', CHANGE commissions commissions JSON NOT NULL COMMENT \'(DC2Type:json)\', CHANGE connection connection JSON NOT NULL COMMENT \'(DC2Type:json)\', CHANGE interest interest JSON NOT NULL COMMENT \'(DC2Type:json)\'');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE account CHANGE withdraw withdraw NUMERIC(11, 2) DEFAULT NULL, CHANGE deposit deposit NUMERIC(11, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE wallet CHANGE invested invested NUMERIC(11, 2) NOT NULL, CHANGE capital capital NUMERIC(11, 2) NOT NULL, CHANGE funds funds NUMERIC(11, 2) NOT NULL, CHANGE dividend dividend NUMERIC(11, 2) NOT NULL, CHANGE commissions commissions NUMERIC(11, 4) NOT NULL, CHANGE connection connection NUMERIC(11, 2) NOT NULL, CHANGE interest interest NUMERIC(11, 2) NOT NULL');
    }
}
