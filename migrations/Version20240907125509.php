<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240907125509 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE card (id INT AUTO_INCREMENT NOT NULL, add_to_id INT NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_161498D38D8640A4 (add_to_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE card ADD CONSTRAINT FK_161498D38D8640A4 FOREIGN KEY (add_to_id) REFERENCES deck (id)');
        $this->addSql('ALTER TABLE card_list DROP FOREIGN KEY FK_A8FBFABA8D8640A4');
        $this->addSql('DROP TABLE card_list');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE card_list (id INT AUTO_INCREMENT NOT NULL, add_to_id INT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_A8FBFABA8D8640A4 (add_to_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE card_list ADD CONSTRAINT FK_A8FBFABA8D8640A4 FOREIGN KEY (add_to_id) REFERENCES deck (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE card DROP FOREIGN KEY FK_161498D38D8640A4');
        $this->addSql('DROP TABLE card');
    }
}
