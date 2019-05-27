<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190523230938 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE stock ADD pe_ratio DOUBLE PRECISION DEFAULT NULL, ADD pre_close NUMERIC(10, 3) DEFAULT NULL, ADD open NUMERIC(10, 3) DEFAULT NULL, ADD day_low NUMERIC(10, 3) DEFAULT NULL, ADD day_high NUMERIC(10, 3) DEFAULT NULL, ADD week_52_low NUMERIC(10, 3) DEFAULT NULL, ADD week_52_high NUMERIC(10, 3) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE stock DROP pe_ratio, DROP pre_close, DROP open, DROP day_low, DROP day_high, DROP week_52_low, DROP week_52_high');
    }
}
