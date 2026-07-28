<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260728214000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute auteurs, volumes et statut sur manga pour que la fiche publique ne depende plus de la source externe';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE manga ADD auteurs JSON DEFAULT NULL, ADD volumes INT DEFAULT NULL, ADD statut VARCHAR(50) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE manga DROP auteurs, DROP volumes, DROP statut');
    }
}
