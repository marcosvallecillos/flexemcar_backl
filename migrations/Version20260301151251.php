<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260301151251 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE reservas_anuladas (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(255) NOT NULL, dia DATE NOT NULL, hora TIME NOT NULL, fecha_anulada DATETIME NOT NULL, user_id_id INT DEFAULT NULL, vehicle_id_id INT DEFAULT NULL, INDEX IDX_AC7BA24F9D86650F (user_id_id), UNIQUE INDEX UNIQ_AC7BA24F1DEB1EBB (vehicle_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE reservas_borradas (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(255) NOT NULL, dia DATE NOT NULL, hora TIME NOT NULL, borrada_en DATETIME NOT NULL, user_id_id INT DEFAULT NULL, vehicle_id_id INT DEFAULT NULL, INDEX IDX_4656B7439D86650F (user_id_id), UNIQUE INDEX UNIQ_4656B7431DEB1EBB (vehicle_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE reservas_anuladas ADD CONSTRAINT FK_AC7BA24F9D86650F FOREIGN KEY (user_id_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE reservas_anuladas ADD CONSTRAINT FK_AC7BA24F1DEB1EBB FOREIGN KEY (vehicle_id_id) REFERENCES vehicles (id)');
        $this->addSql('ALTER TABLE reservas_borradas ADD CONSTRAINT FK_4656B7439D86650F FOREIGN KEY (user_id_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE reservas_borradas ADD CONSTRAINT FK_4656B7431DEB1EBB FOREIGN KEY (vehicle_id_id) REFERENCES vehicles (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reservas_anuladas DROP FOREIGN KEY FK_AC7BA24F9D86650F');
        $this->addSql('ALTER TABLE reservas_anuladas DROP FOREIGN KEY FK_AC7BA24F1DEB1EBB');
        $this->addSql('ALTER TABLE reservas_borradas DROP FOREIGN KEY FK_4656B7439D86650F');
        $this->addSql('ALTER TABLE reservas_borradas DROP FOREIGN KEY FK_4656B7431DEB1EBB');
        $this->addSql('DROP TABLE reservas_anuladas');
        $this->addSql('DROP TABLE reservas_borradas');
    }
}
