<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250131150254 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE revision DROP CONSTRAINT FK_6D6315CCC5D16576');
        $this->addSql('ALTER TABLE revision ADD CONSTRAINT FK_6D6315CCC5D16576 FOREIGN KEY (flashcard_id) REFERENCES flashcard (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE revision DROP CONSTRAINT fk_6d6315ccc5d16576');
        $this->addSql('ALTER TABLE revision ADD CONSTRAINT fk_6d6315ccc5d16576 FOREIGN KEY (flashcard_id) REFERENCES flashcard (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
