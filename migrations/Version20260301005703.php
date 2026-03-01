<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260301005703 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE favorites DROP FOREIGN KEY `FK_E46960F5545317D1`');
        $this->addSql('ALTER TABLE favorites DROP FOREIGN KEY `FK_E46960F5A76ED395`');
        $this->addSql('DROP INDEX fk_e46960f5a76ed395 ON favorites');
        $this->addSql('CREATE INDEX IDX_E46960F5A76ED395 ON favorites (user_id)');
        $this->addSql('DROP INDEX fk_e46960f5545317d1 ON favorites');
        $this->addSql('CREATE INDEX IDX_E46960F5545317D1 ON favorites (vehicle_id)');
        $this->addSql('ALTER TABLE favorites ADD CONSTRAINT `FK_E46960F5545317D1` FOREIGN KEY (vehicle_id) REFERENCES vehicles (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE favorites ADD CONSTRAINT `FK_E46960F5A76ED395` FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE favorites DROP FOREIGN KEY FK_E46960F5A76ED395');
        $this->addSql('ALTER TABLE favorites DROP FOREIGN KEY FK_E46960F5545317D1');
        $this->addSql('DROP INDEX idx_e46960f5a76ed395 ON favorites');
        $this->addSql('CREATE INDEX FK_E46960F5A76ED395 ON favorites (user_id)');
        $this->addSql('DROP INDEX idx_e46960f5545317d1 ON favorites');
        $this->addSql('CREATE INDEX FK_E46960F5545317D1 ON favorites (vehicle_id)');
        $this->addSql('ALTER TABLE favorites ADD CONSTRAINT FK_E46960F5A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE favorites ADD CONSTRAINT FK_E46960F5545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicles (id) ON DELETE CASCADE');
    }
}
