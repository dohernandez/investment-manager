<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190713222813 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE trade CHANGE buy_paid buy_paid JSON DEFAULT NULL COMMENT \'(DC2Type:json)\', CHANGE sell_paid sell_paid JSON DEFAULT NULL COMMENT \'(DC2Type:json)\', CHANGE capital capital JSON DEFAULT NULL COMMENT \'(DC2Type:json)\', CHANGE dividend dividend JSON DEFAULT NULL COMMENT \'(DC2Type:json)\', CHANGE net net JSON DEFAULT NULL COMMENT \'(DC2Type:json)\', CHANGE invested invested JSON DEFAULT NULL COMMENT \'(DC2Type:json)\'');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE trade CHANGE buy_paid buy_paid NUMERIC(11, 2) DEFAULT NULL, CHANGE sell_paid sell_paid NUMERIC(11, 2) DEFAULT NULL, CHANGE invested invested NUMERIC(11, 2) NOT NULL, CHANGE capital capital NUMERIC(11, 2) NOT NULL, CHANGE dividend dividend NUMERIC(11, 2) DEFAULT NULL, CHANGE net net NUMERIC(11, 2) NOT NULL');
    }
}
