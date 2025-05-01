<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250126162748 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE revision ADD due_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE revision ADD rating INT DEFAULT NULL');
        $this->addSql('ALTER TABLE revision ADD state INT DEFAULT NULL');
        $this->addSql('ALTER TABLE revision ADD step INT DEFAULT NULL');
        $this->addSql('ALTER TABLE revision DROP status');
        $this->addSql('ALTER TABLE revision RENAME COLUMN ease_factor TO difficulty');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE revision ADD status VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE revision DROP due_date');
        $this->addSql('ALTER TABLE revision DROP rating');
        $this->addSql('ALTER TABLE revision DROP state');
        $this->addSql('ALTER TABLE revision DROP step');
        $this->addSql('ALTER TABLE revision RENAME COLUMN difficulty TO ease_factor');
    }
}
