<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251105171624 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_level (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, level_id INT NOT NULL, score INT DEFAULT 0 NOT NULL, completed TINYINT(1) DEFAULT 0 NOT NULL, completed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_7828374BA76ED395 (user_id), INDEX IDX_7828374B5FB14BA7 (level_id), UNIQUE INDEX unique_user_level (user_id, level_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_level ADD CONSTRAINT FK_7828374BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_level ADD CONSTRAINT FK_7828374B5FB14BA7 FOREIGN KEY (level_id) REFERENCES level (id)');
        $this->addSql('ALTER TABLE user_language DROP FOREIGN KEY FK_345695B5A76ED395');
        $this->addSql('ALTER TABLE user_language DROP FOREIGN KEY FK_345695B582F1BAF4');
        $this->addSql('DROP TABLE user_language');
        $this->addSql('ALTER TABLE lesson DROP number, CHANGE content content LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE level DROP score');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_language (user_id INT NOT NULL, language_id INT NOT NULL, INDEX IDX_345695B582F1BAF4 (language_id), INDEX IDX_345695B5A76ED395 (user_id), PRIMARY KEY(user_id, language_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE user_language ADD CONSTRAINT FK_345695B5A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_language ADD CONSTRAINT FK_345695B582F1BAF4 FOREIGN KEY (language_id) REFERENCES language (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_level DROP FOREIGN KEY FK_7828374BA76ED395');
        $this->addSql('ALTER TABLE user_level DROP FOREIGN KEY FK_7828374B5FB14BA7');
        $this->addSql('DROP TABLE user_level');
        $this->addSql('ALTER TABLE level ADD score INT NOT NULL');
        $this->addSql('ALTER TABLE lesson ADD number INT NOT NULL, CHANGE content content JSON DEFAULT NULL COMMENT \'(DC2Type:json)\'');
    }
}
