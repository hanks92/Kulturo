<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241111120612 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE deck_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE flashcard_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE deck (id INT NOT NULL, owner_id INT NOT NULL, title VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_4FAC36377E3C61F9 ON deck (owner_id)');
        $this->addSql('COMMENT ON COLUMN deck.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN deck.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE flashcard (id INT NOT NULL, deck_id INT NOT NULL, question VARCHAR(255) DEFAULT NULL, answer VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_70511A09111948DC ON flashcard (deck_id)');
        $this->addSql('ALTER TABLE deck ADD CONSTRAINT FK_4FAC36377E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE flashcard ADD CONSTRAINT FK_70511A09111948DC FOREIGN KEY (deck_id) REFERENCES deck (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE deck_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE flashcard_id_seq CASCADE');
        $this->addSql('ALTER TABLE deck DROP CONSTRAINT FK_4FAC36377E3C61F9');
        $this->addSql('ALTER TABLE flashcard DROP CONSTRAINT FK_70511A09111948DC');
        $this->addSql('DROP TABLE deck');
        $this->addSql('DROP TABLE flashcard');
    }
}
