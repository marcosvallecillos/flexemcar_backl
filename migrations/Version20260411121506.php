<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260411121506 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE Favorites (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME DEFAULT NULL, isFavorite TINYINT DEFAULT 0 NOT NULL, user_id INT NOT NULL, vehicle_id VARCHAR(20) NOT NULL, INDEX IDX_6698E256A76ED395 (user_id), INDEX IDX_6698E256545317D1 (vehicle_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE ReservasAnuladas (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(255) NOT NULL, dia DATE NOT NULL, hora TIME NOT NULL, fecha_anulada DATETIME NOT NULL, user_id INT DEFAULT NULL, vehicle_id VARCHAR(20) DEFAULT NULL, INDEX IDX_3EF9CF63A76ED395 (user_id), INDEX IDX_3EF9CF63545317D1 (vehicle_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE ReservasBorradas (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(255) NOT NULL, dia DATE NOT NULL, hora TIME NOT NULL, borradaEn DATETIME NOT NULL, user_id_id INT DEFAULT NULL, vehicle_id VARCHAR(20) DEFAULT NULL, INDEX IDX_D4D4DA6F9D86650F (user_id_id), INDEX IDX_D4D4DA6F545317D1 (vehicle_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE Reviews (id INT AUTO_INCREMENT NOT NULL, rating INT NOT NULL, comment VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, usuario_id_id INT DEFAULT NULL, reserva_id_id INT DEFAULT NULL, INDEX IDX_A6CDD293629AF449 (usuario_id_id), UNIQUE INDEX UNIQ_A6CDD293E83688EE (reserva_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE reservas (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(255) DEFAULT NULL, dia DATE NOT NULL, hora TIME NOT NULL, created_at DATETIME DEFAULT NULL, user_id INT NOT NULL, vehicle_id VARCHAR(20) NOT NULL, review_id INT DEFAULT NULL, INDEX IDX_AA1DAB01A76ED395 (user_id), INDEX IDX_AA1DAB01545317D1 (vehicle_id), UNIQUE INDEX UNIQ_AA1DAB013E2E969B (review_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, password VARCHAR(255) DEFAULT NULL, telefono INT DEFAULT NULL, rol VARCHAR(50) DEFAULT NULL, created_at DATETIME DEFAULT NULL, last_login DATETIME DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE vehicles_images (id INT AUTO_INCREMENT NOT NULL, image_url VARCHAR(255) DEFAULT NULL, vehicle_id VARCHAR(20) NOT NULL, INDEX IDX_A886739F545317D1 (vehicle_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE Favorites ADD CONSTRAINT FK_6698E256A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE Favorites ADD CONSTRAINT FK_6698E256545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle_models (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ReservasAnuladas ADD CONSTRAINT FK_3EF9CF63A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE ReservasAnuladas ADD CONSTRAINT FK_3EF9CF63545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle_models (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE ReservasBorradas ADD CONSTRAINT FK_D4D4DA6F9D86650F FOREIGN KEY (user_id_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE ReservasBorradas ADD CONSTRAINT FK_D4D4DA6F545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle_models (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE Reviews ADD CONSTRAINT FK_A6CDD293629AF449 FOREIGN KEY (usuario_id_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE Reviews ADD CONSTRAINT FK_A6CDD293E83688EE FOREIGN KEY (reserva_id_id) REFERENCES reservas (id)');
        $this->addSql('ALTER TABLE reservas ADD CONSTRAINT FK_AA1DAB01A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reservas ADD CONSTRAINT FK_AA1DAB01545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle_models (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reservas ADD CONSTRAINT FK_AA1DAB013E2E969B FOREIGN KEY (review_id) REFERENCES Reviews (id)');
        $this->addSql('ALTER TABLE vehicles_images ADD CONSTRAINT FK_A886739F545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle_models (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE appointments');
        $this->addSql('DROP TABLE conversation_logs');
        $this->addSql('DROP TABLE leads');
        $this->addSql('DROP TABLE reservas_anuladas');
        $this->addSql('DROP TABLE reservas_borradas');
        $this->addSql('DROP TABLE sessions');
        $this->addSql('ALTER TABLE vehicle_models CHANGE name name VARCHAR(150) DEFAULT NULL, CHANGE type type VARCHAR(30) DEFAULT NULL, CHANGE brand brand VARCHAR(100) DEFAULT NULL, CHANGE price price DOUBLE PRECISION DEFAULT NULL, CHANGE price_str price_str VARCHAR(50) DEFAULT NULL, CHANGE description description LONGTEXT DEFAULT NULL, CHANGE features features LONGTEXT DEFAULT NULL, CHANGE van_category van_category VARCHAR(50) DEFAULT NULL, CHANGE van_size van_size VARCHAR(50) DEFAULT NULL, CHANGE is_favorite is_favorite TINYINT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE appointments (id INT AUTO_INCREMENT NOT NULL, whatsapp_id VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, lead_name VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, email VARCHAR(150) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, lead_phone VARCHAR(20) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, model_interest VARCHAR(200) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, date VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, time VARCHAR(20) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, confirmed TINYINT DEFAULT NULL, notes TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, created_at DATETIME DEFAULT NULL, INDEX ix_appointments_id (id), INDEX ix_appointments_whatsapp_id (whatsapp_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE conversation_logs (id INT AUTO_INCREMENT NOT NULL, whatsapp_id VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, direction VARCHAR(10) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, message_type VARCHAR(20) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, content TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, timestamp DATETIME DEFAULT NULL, INDEX ix_conversation_logs_id (id), INDEX ix_conversation_logs_whatsapp_id (whatsapp_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE leads (id INT AUTO_INCREMENT NOT NULL, whatsapp_id VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, phone VARCHAR(20) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, name VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, email VARCHAR(150) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, model_interest VARCHAR(200) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, budget FLOAT DEFAULT NULL, status ENUM(\'NEW\', \'CONTACTED\', \'INTERESTED\', \'APPOINTMENT\', \'CLOSED\', \'LOST\') CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, notes TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, appointment_date VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, appointment_time VARCHAR(20) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, van_category ENUM(\'CARGA\', \'PLAZAS\') CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, van_size ENUM(\'L1H1\', \'L2H2\', \'L3H2\', \'L2H3\', \'L3H3\') CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, price_min FLOAT DEFAULT NULL, price_max FLOAT DEFAULT NULL, financing_apt TINYINT DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX ix_leads_whatsapp_id (whatsapp_id), INDEX ix_leads_id (id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE reservas_anuladas (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, dia DATE NOT NULL, hora TIME NOT NULL, fecha_anulada DATETIME NOT NULL, user_id INT DEFAULT NULL, vehicle_id INT DEFAULT NULL, INDEX IDX_AC7BA24FA76ED395 (user_id), INDEX IDX_AC7BA24F545317D1 (vehicle_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE reservas_borradas (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, dia DATE NOT NULL, hora TIME NOT NULL, borrada_en DATETIME NOT NULL, user_id_id INT DEFAULT NULL, vehicle_id INT DEFAULT NULL, INDEX IDX_4656B7439D86650F (user_id_id), INDEX IDX_4656B743545317D1 (vehicle_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE sessions (id INT AUTO_INCREMENT NOT NULL, whatsapp_id VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, state VARCHAR(60) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, context TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, last_activity DATETIME DEFAULT NULL, created_at DATETIME DEFAULT NULL, UNIQUE INDEX ix_sessions_whatsapp_id (whatsapp_id), INDEX ix_sessions_id (id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE Favorites DROP FOREIGN KEY FK_6698E256A76ED395');
        $this->addSql('ALTER TABLE Favorites DROP FOREIGN KEY FK_6698E256545317D1');
        $this->addSql('ALTER TABLE ReservasAnuladas DROP FOREIGN KEY FK_3EF9CF63A76ED395');
        $this->addSql('ALTER TABLE ReservasAnuladas DROP FOREIGN KEY FK_3EF9CF63545317D1');
        $this->addSql('ALTER TABLE ReservasBorradas DROP FOREIGN KEY FK_D4D4DA6F9D86650F');
        $this->addSql('ALTER TABLE ReservasBorradas DROP FOREIGN KEY FK_D4D4DA6F545317D1');
        $this->addSql('ALTER TABLE Reviews DROP FOREIGN KEY FK_A6CDD293629AF449');
        $this->addSql('ALTER TABLE Reviews DROP FOREIGN KEY FK_A6CDD293E83688EE');
        $this->addSql('ALTER TABLE reservas DROP FOREIGN KEY FK_AA1DAB01A76ED395');
        $this->addSql('ALTER TABLE reservas DROP FOREIGN KEY FK_AA1DAB01545317D1');
        $this->addSql('ALTER TABLE reservas DROP FOREIGN KEY FK_AA1DAB013E2E969B');
        $this->addSql('ALTER TABLE vehicles_images DROP FOREIGN KEY FK_A886739F545317D1');
        $this->addSql('DROP TABLE Favorites');
        $this->addSql('DROP TABLE ReservasAnuladas');
        $this->addSql('DROP TABLE ReservasBorradas');
        $this->addSql('DROP TABLE Reviews');
        $this->addSql('DROP TABLE reservas');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE vehicles_images');
        $this->addSql('ALTER TABLE vehicle_models CHANGE name name VARCHAR(150) NOT NULL, CHANGE type type VARCHAR(30) NOT NULL, CHANGE brand brand VARCHAR(100) NOT NULL, CHANGE price price FLOAT NOT NULL, CHANGE price_str price_str VARCHAR(50) NOT NULL, CHANGE description description TEXT DEFAULT NULL, CHANGE features features TEXT DEFAULT NULL, CHANGE van_category van_category ENUM(\'CARGA\', \'PLAZAS\') DEFAULT NULL, CHANGE van_size van_size ENUM(\'L1H1\', \'L2H2\', \'L3H2\', \'L2H3\', \'L3H3\') DEFAULT NULL, CHANGE is_favorite is_favorite TINYINT DEFAULT 0 NOT NULL');
    }
}
