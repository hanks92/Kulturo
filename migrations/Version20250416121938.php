<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250416121938 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_stats DROP CONSTRAINT fk_b5859cf24a3353d8');
        $this->addSql('DROP INDEX uniq_b5859cf24a3353d8');
        $this->addSql('ALTER TABLE user_stats ADD user_id INT NOT NULL');
        $this->addSql('ALTER TABLE user_stats DROP app_user_id');
        $this->addSql('ALTER TABLE user_stats ADD CONSTRAINT FK_B5859CF2A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B5859CF2A76ED395 ON user_stats (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE user_stats DROP CONSTRAINT FK_B5859CF2A76ED395');
        $this->addSql('DROP INDEX UNIQ_B5859CF2A76ED395');
        $this->addSql('ALTER TABLE user_stats ADD app_user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user_stats DROP user_id');
        $this->addSql('ALTER TABLE user_stats ADD CONSTRAINT fk_b5859cf24a3353d8 FOREIGN KEY (app_user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_b5859cf24a3353d8 ON user_stats (app_user_id)');
    }
}
