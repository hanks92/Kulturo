<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241118105504 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE revision_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE revision (id INT NOT NULL, flashcard_id INT DEFAULT NULL, review_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, interval INT DEFAULT NULL, ease_factor DOUBLE PRECISION DEFAULT NULL, status VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_6D6315CCC5D16576 ON revision (flashcard_id)');
        $this->addSql('ALTER TABLE revision ADD CONSTRAINT FK_6D6315CCC5D16576 FOREIGN KEY (flashcard_id) REFERENCES flashcard (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE revision_id_seq CASCADE');
        $this->addSql('ALTER TABLE revision DROP CONSTRAINT FK_6D6315CCC5D16576');
        $this->addSql('DROP TABLE revision');
    }
}
