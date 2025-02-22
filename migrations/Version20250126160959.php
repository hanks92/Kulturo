<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250126160959 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE revision ADD review_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE revision DROP last_review');
        $this->addSql('ALTER TABLE revision DROP "interval"');
        $this->addSql('ALTER TABLE revision DROP due_date');
        $this->addSql('ALTER TABLE revision DROP state');
        $this->addSql('ALTER TABLE revision DROP step');
        $this->addSql('ALTER TABLE revision ALTER flashcard_id DROP NOT NULL');
        $this->addSql('ALTER TABLE revision RENAME COLUMN difficulty TO ease_factor');
        $this->addSql('ALTER TABLE revision RENAME COLUMN rating TO status');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE revision ADD "interval" INT DEFAULT NULL');
        $this->addSql('ALTER TABLE revision ADD due_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE revision ADD state INT DEFAULT NULL');
        $this->addSql('ALTER TABLE revision ADD step INT DEFAULT NULL');
        $this->addSql('ALTER TABLE revision ALTER flashcard_id SET NOT NULL');
        $this->addSql('ALTER TABLE revision RENAME COLUMN review_date TO last_review');
        $this->addSql('ALTER TABLE revision RENAME COLUMN ease_factor TO difficulty');
        $this->addSql('ALTER TABLE revision RENAME COLUMN status TO rating');
    }
}
