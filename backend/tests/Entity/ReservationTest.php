<?php

namespace App\Tests\Entity;

use App\Entity\Commande;
use App\Entity\Reservation;
use App\Entity\Tome;
use PHPUnit\Framework\TestCase;

class ReservationTest extends TestCase
{
    public function testTomeGetterSetter(): void
    {
        $reservation = new Reservation();
        $tome = new Tome();
        $tome->setNumeroTome(3);

        $reservation->setTome($tome);

        $this->assertSame($tome, $reservation->getTome());
        $this->assertSame(3, $reservation->getTome()->getNumeroTome());
    }

    public function testTomeCanBeNull(): void
    {
        $reservation = new Reservation();
        $reservation->setTome(null);

        $this->assertNull($reservation->getTome());
    }

    public function testCommandeGetterSetter(): void
    {
        $reservation = new Reservation();
        $commande = new Commande();

        $reservation->setCommande($commande);

        $this->assertSame($commande, $reservation->getCommande());
    }

    public function testCommandeCanBeNull(): void
    {
        $reservation = new Reservation();
        $reservation->setCommande(null);

        $this->assertNull($reservation->getCommande());
    }

    public function testIdIsNullBeforePersistence(): void
    {
        $reservation = new Reservation();

        $this->assertNull($reservation->getId());
    }
}
