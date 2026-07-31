<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260731155612 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE UNIQUE INDEX unique_favori_utilisateur_manga ON favori (utilisateur_id, manga_id)');
        $this->addSql('ALTER TABLE tome CHANGE prix prix NUMERIC(5, 2) DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX unique_favori_utilisateur_manga ON favori');
        $this->addSql('ALTER TABLE tome CHANGE prix prix NUMERIC(5, 2) DEFAULT \'0.00\' NOT NULL');
    }
}
