<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250426153010 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE user_plant_inventory_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE user_plant_inventory (id INT NOT NULL, user_app_id INT DEFAULT NULL, plant_type VARCHAR(255) DEFAULT NULL, quantity INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_26BDB5211CD53A10 ON user_plant_inventory (user_app_id)');
        $this->addSql('ALTER TABLE user_plant_inventory ADD CONSTRAINT FK_26BDB5211CD53A10 FOREIGN KEY (user_app_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE user_plant_inventory_id_seq CASCADE');
        $this->addSql('ALTER TABLE user_plant_inventory DROP CONSTRAINT FK_26BDB5211CD53A10');
        $this->addSql('DROP TABLE user_plant_inventory');
    }
}
