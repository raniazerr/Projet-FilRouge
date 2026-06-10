<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260610113455 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE manga ADD synopsis LONGTEXT DEFAULT NULL, DROP reservation_id');
        $this->addSql('ALTER TABLE tome CHANGE prix prix NUMERIC(5, 2) DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE manga ADD reservation_id INT DEFAULT NULL, DROP synopsis');
        $this->addSql('ALTER TABLE manga ADD CONSTRAINT `FK_765A9E03B83297E7` FOREIGN KEY (reservation_id) REFERENCES reservation (id)');
        $this->addSql('CREATE INDEX IDX_765A9E03B83297E7 ON manga (reservation_id)');
        $this->addSql('ALTER TABLE tome CHANGE prix prix NUMERIC(5, 2) DEFAULT \'0.00\' NOT NULL');
    }
}
