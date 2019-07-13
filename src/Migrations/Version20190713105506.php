<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190713105506 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE operation CHANGE price price JSON DEFAULT NULL COMMENT \'(DC2Type:json)\', CHANGE price_change price_change JSON DEFAULT NULL COMMENT \'(DC2Type:json)\', CHANGE price_change_commission price_change_commission JSON DEFAULT NULL COMMENT \'(DC2Type:json)\', CHANGE value value JSON NOT NULL COMMENT \'(DC2Type:json)\', CHANGE commission commission JSON DEFAULT NULL COMMENT \'(DC2Type:json)\'');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE operation CHANGE price price NUMERIC(11, 2) DEFAULT NULL, CHANGE price_change price_change NUMERIC(11, 4) DEFAULT NULL, CHANGE price_change_commission price_change_commission NUMERIC(11, 4) DEFAULT NULL, CHANGE value value NUMERIC(11, 2) NOT NULL, CHANGE commission commission NUMERIC(11, 4) DEFAULT NULL');
    }
}
