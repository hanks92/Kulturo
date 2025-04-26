<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250426085206 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE garden_plant_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE garden_plant (id INT NOT NULL, user_app_id INT DEFAULT NULL, x INT DEFAULT NULL, y INT DEFAULT NULL, type VARCHAR(255) DEFAULT NULL, level INT DEFAULT NULL, water_received INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_960D56371CD53A10 ON garden_plant (user_app_id)');
        $this->addSql('ALTER TABLE garden_plant ADD CONSTRAINT FK_960D56371CD53A10 FOREIGN KEY (user_app_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE garden_plant_id_seq CASCADE');
        $this->addSql('ALTER TABLE garden_plant DROP CONSTRAINT FK_960D56371CD53A10');
        $this->addSql('DROP TABLE garden_plant');
    }
}
