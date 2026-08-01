<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260801221610 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tome CHANGE prix prix NUMERIC(5, 2) DEFAULT 0 NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX unique_tome_manga_numero ON tome (manga_id, numero_tome)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX unique_tome_manga_numero ON tome');
        $this->addSql('ALTER TABLE tome CHANGE prix prix NUMERIC(5, 2) DEFAULT \'0.00\' NOT NULL');
    }
}
