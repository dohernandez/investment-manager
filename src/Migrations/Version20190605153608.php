<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190605153608 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE position (id INT AUTO_INCREMENT NOT NULL, stock_id INT NOT NULL, amount INT NOT NULL, invested NUMERIC(11, 2) NOT NULL, dividend NUMERIC(11, 4) DEFAULT NULL, buy NUMERIC(11, 2) NOT NULL, sell NUMERIC(11, 2) DEFAULT NULL, dividend_retention NUMERIC(11, 4) DEFAULT NULL, INDEX IDX_462CE4F5DCD6110 (stock_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE position ADD CONSTRAINT FK_462CE4F5DCD6110 FOREIGN KEY (stock_id) REFERENCES stock (id)');
        $this->addSql('ALTER TABLE operation ADD position_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE operation ADD CONSTRAINT FK_1981A66DDD842E46 FOREIGN KEY (position_id) REFERENCES position (id)');
        $this->addSql('CREATE INDEX IDX_1981A66DDD842E46 ON operation (position_id)');
        $this->addSql('ALTER TABLE trade ADD position_id INT NOT NULL, ADD invested NUMERIC(11, 2) NOT NULL');
        $this->addSql('ALTER TABLE trade ADD CONSTRAINT FK_7E1A4366DD842E46 FOREIGN KEY (position_id) REFERENCES position (id)');
        $this->addSql('CREATE INDEX IDX_7E1A4366DD842E46 ON trade (position_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE operation DROP FOREIGN KEY FK_1981A66DDD842E46');
        $this->addSql('ALTER TABLE trade DROP FOREIGN KEY FK_7E1A4366DD842E46');
        $this->addSql('DROP TABLE position');
        $this->addSql('DROP INDEX IDX_1981A66DDD842E46 ON operation');
        $this->addSql('ALTER TABLE operation DROP position_id');
        $this->addSql('DROP INDEX IDX_7E1A4366DD842E46 ON trade');
        $this->addSql('ALTER TABLE trade DROP position_id, DROP invested');
    }
}
