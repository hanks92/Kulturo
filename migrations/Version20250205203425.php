<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250205203425 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE review_log_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE review_log (id INT NOT NULL, revision_id INT NOT NULL, rating INT NOT NULL, review_date_time TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, review_duration INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8308640C1DFA7C8F ON review_log (revision_id)');
        $this->addSql('ALTER TABLE review_log ADD CONSTRAINT FK_8308640C1DFA7C8F FOREIGN KEY (revision_id) REFERENCES revision (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE review_log_id_seq CASCADE');
        $this->addSql('ALTER TABLE review_log DROP CONSTRAINT FK_8308640C1DFA7C8F');
        $this->addSql('DROP TABLE review_log');
    }
}
