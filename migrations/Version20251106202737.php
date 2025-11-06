<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251106202737 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_skin (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, skin_id INT NOT NULL, unlocked TINYINT(1) DEFAULT 0 NOT NULL, is_current TINYINT(1) DEFAULT 0 NOT NULL, INDEX IDX_78F824D7A76ED395 (user_id), INDEX IDX_78F824D7F404637F (skin_id), UNIQUE INDEX unique_user_skin (user_id, skin_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_skin ADD CONSTRAINT FK_78F824D7A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_skin ADD CONSTRAINT FK_78F824D7F404637F FOREIGN KEY (skin_id) REFERENCES skin (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_skin DROP FOREIGN KEY FK_78F824D7A76ED395');
        $this->addSql('ALTER TABLE user_skin DROP FOREIGN KEY FK_78F824D7F404637F');
        $this->addSql('DROP TABLE user_skin');
    }
}
