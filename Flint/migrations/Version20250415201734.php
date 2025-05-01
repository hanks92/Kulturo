<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250415201734 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE achievement_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE user_achievement_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE user_stats_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE achievement (id INT NOT NULL, code VARCHAR(255) DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, description TEXT DEFAULT NULL, rewards JSON DEFAULT NULL, is_premium BOOLEAN DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE user_achievement (id INT NOT NULL, app_user_id INT DEFAULT NULL, achievement_id INT DEFAULT NULL, achieved_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3F68B6644A3353D8 ON user_achievement (app_user_id)');
        $this->addSql('CREATE INDEX IDX_3F68B664B3EC99FE ON user_achievement (achievement_id)');
        $this->addSql('COMMENT ON COLUMN user_achievement.achieved_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE user_stats (id INT NOT NULL, app_user_id INT DEFAULT NULL, streak INT DEFAULT NULL, max_streak INT DEFAULT NULL, last_activity TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, total_xp INT DEFAULT NULL, cards_reviewed INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B5859CF24A3353D8 ON user_stats (app_user_id)');
        $this->addSql('COMMENT ON COLUMN user_stats.last_activity IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE user_achievement ADD CONSTRAINT FK_3F68B6644A3353D8 FOREIGN KEY (app_user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_achievement ADD CONSTRAINT FK_3F68B664B3EC99FE FOREIGN KEY (achievement_id) REFERENCES achievement (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_stats ADD CONSTRAINT FK_B5859CF24A3353D8 FOREIGN KEY (app_user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE achievement_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE user_achievement_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE user_stats_id_seq CASCADE');
        $this->addSql('ALTER TABLE user_achievement DROP CONSTRAINT FK_3F68B6644A3353D8');
        $this->addSql('ALTER TABLE user_achievement DROP CONSTRAINT FK_3F68B664B3EC99FE');
        $this->addSql('ALTER TABLE user_stats DROP CONSTRAINT FK_B5859CF24A3353D8');
        $this->addSql('DROP TABLE achievement');
        $this->addSql('DROP TABLE user_achievement');
        $this->addSql('DROP TABLE user_stats');
    }
}
