<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260228192008 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE favorites_vehicles DROP FOREIGN KEY `FK_AE7F5A5616F10C70`');
        $this->addSql('ALTER TABLE favorites_vehicles DROP FOREIGN KEY `FK_AE7F5A5684DDC6B4`');
        $this->addSql('DROP TABLE favorites_vehicles');
        $this->addSql('ALTER TABLE favorites ADD vehicle_id_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE favorites ADD CONSTRAINT FK_E46960F51DEB1EBB FOREIGN KEY (vehicle_id_id) REFERENCES vehicles (id)');
        $this->addSql('CREATE INDEX IDX_E46960F51DEB1EBB ON favorites (vehicle_id_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE favorites_vehicles (favorites_id INT NOT NULL, vehicles_id INT NOT NULL, INDEX IDX_AE7F5A5616F10C70 (vehicles_id), INDEX IDX_AE7F5A5684DDC6B4 (favorites_id), PRIMARY KEY (favorites_id, vehicles_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE favorites_vehicles ADD CONSTRAINT `FK_AE7F5A5616F10C70` FOREIGN KEY (vehicles_id) REFERENCES vehicles (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE favorites_vehicles ADD CONSTRAINT `FK_AE7F5A5684DDC6B4` FOREIGN KEY (favorites_id) REFERENCES favorites (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE favorites DROP FOREIGN KEY FK_E46960F51DEB1EBB');
        $this->addSql('DROP INDEX IDX_E46960F51DEB1EBB ON favorites');
        $this->addSql('ALTER TABLE favorites DROP vehicle_id_id');
    }
}
