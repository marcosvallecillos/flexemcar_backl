<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260328175535 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE favorites (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME DEFAULT NULL, is_favorite TINYINT DEFAULT 0 NOT NULL, user_id INT NOT NULL, vehicle_id INT NOT NULL, INDEX IDX_E46960F5A76ED395 (user_id), INDEX IDX_E46960F5545317D1 (vehicle_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE reservas (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(255) DEFAULT NULL, dia DATE NOT NULL, hora TIME NOT NULL, created_at DATETIME DEFAULT NULL, user_id INT NOT NULL, vehicle_id INT NOT NULL, review_id INT DEFAULT NULL, INDEX IDX_AA1DAB01A76ED395 (user_id), INDEX IDX_AA1DAB01545317D1 (vehicle_id), UNIQUE INDEX UNIQ_AA1DAB013E2E969B (review_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE reservas_anuladas (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(255) NOT NULL, dia DATE NOT NULL, hora TIME NOT NULL, fecha_anulada DATETIME NOT NULL, user_id INT DEFAULT NULL, vehicle_id INT DEFAULT NULL, INDEX IDX_AC7BA24FA76ED395 (user_id), INDEX IDX_AC7BA24F545317D1 (vehicle_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE reservas_borradas (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(255) NOT NULL, dia DATE NOT NULL, hora TIME NOT NULL, borrada_en DATETIME NOT NULL, user_id_id INT DEFAULT NULL, vehicle_id INT DEFAULT NULL, INDEX IDX_4656B7439D86650F (user_id_id), INDEX IDX_4656B743545317D1 (vehicle_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE reviews (id INT AUTO_INCREMENT NOT NULL, rating INT NOT NULL, comment VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, usuario_id_id INT DEFAULT NULL, reserva_id_id INT DEFAULT NULL, INDEX IDX_6970EB0F629AF449 (usuario_id_id), UNIQUE INDEX UNIQ_6970EB0FE83688EE (reserva_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, password VARCHAR(255) DEFAULT NULL, telefono INT DEFAULT NULL, rol VARCHAR(50) DEFAULT NULL, created_at DATETIME DEFAULT NULL, last_login DATETIME DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE vehicles (id INT AUTO_INCREMENT NOT NULL, model VARCHAR(100) DEFAULT NULL, marca VARCHAR(255) DEFAULT NULL, price NUMERIC(10, 2) DEFAULT NULL, motor VARCHAR(255) DEFAULT NULL, km INT DEFAULT NULL, year INT DEFAULT NULL, description VARCHAR(255) NOT NULL, is_favorite TINYINT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE vehicles_images (id INT AUTO_INCREMENT NOT NULL, image_url VARCHAR(255) DEFAULT NULL, vehicle_id INT NOT NULL, INDEX IDX_A886739F545317D1 (vehicle_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE favorites ADD CONSTRAINT FK_E46960F5A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE favorites ADD CONSTRAINT FK_E46960F5545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicles (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reservas ADD CONSTRAINT FK_AA1DAB01A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reservas ADD CONSTRAINT FK_AA1DAB01545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicles (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reservas ADD CONSTRAINT FK_AA1DAB013E2E969B FOREIGN KEY (review_id) REFERENCES reviews (id)');
        $this->addSql('ALTER TABLE reservas_anuladas ADD CONSTRAINT FK_AC7BA24FA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE reservas_anuladas ADD CONSTRAINT FK_AC7BA24F545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicles (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE reservas_borradas ADD CONSTRAINT FK_4656B7439D86650F FOREIGN KEY (user_id_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE reservas_borradas ADD CONSTRAINT FK_4656B743545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicles (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE reviews ADD CONSTRAINT FK_6970EB0F629AF449 FOREIGN KEY (usuario_id_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE reviews ADD CONSTRAINT FK_6970EB0FE83688EE FOREIGN KEY (reserva_id_id) REFERENCES reservas (id)');
        $this->addSql('ALTER TABLE vehicles_images ADD CONSTRAINT FK_A886739F545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicles (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE favorites DROP FOREIGN KEY FK_E46960F5A76ED395');
        $this->addSql('ALTER TABLE favorites DROP FOREIGN KEY FK_E46960F5545317D1');
        $this->addSql('ALTER TABLE reservas DROP FOREIGN KEY FK_AA1DAB01A76ED395');
        $this->addSql('ALTER TABLE reservas DROP FOREIGN KEY FK_AA1DAB01545317D1');
        $this->addSql('ALTER TABLE reservas DROP FOREIGN KEY FK_AA1DAB013E2E969B');
        $this->addSql('ALTER TABLE reservas_anuladas DROP FOREIGN KEY FK_AC7BA24FA76ED395');
        $this->addSql('ALTER TABLE reservas_anuladas DROP FOREIGN KEY FK_AC7BA24F545317D1');
        $this->addSql('ALTER TABLE reservas_borradas DROP FOREIGN KEY FK_4656B7439D86650F');
        $this->addSql('ALTER TABLE reservas_borradas DROP FOREIGN KEY FK_4656B743545317D1');
        $this->addSql('ALTER TABLE reviews DROP FOREIGN KEY FK_6970EB0F629AF449');
        $this->addSql('ALTER TABLE reviews DROP FOREIGN KEY FK_6970EB0FE83688EE');
        $this->addSql('ALTER TABLE vehicles_images DROP FOREIGN KEY FK_A886739F545317D1');
        $this->addSql('DROP TABLE favorites');
        $this->addSql('DROP TABLE reservas');
        $this->addSql('DROP TABLE reservas_anuladas');
        $this->addSql('DROP TABLE reservas_borradas');
        $this->addSql('DROP TABLE reviews');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE vehicles');
        $this->addSql('DROP TABLE vehicles_images');
    }
}
