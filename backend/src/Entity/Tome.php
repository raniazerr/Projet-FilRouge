<?php

namespace App\Entity;

use App\Repository\TomeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TomeRepository::class)]
class Tome
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private int $numero_tome = 1;

    #[ORM\Column(options: ['default' => 0])]
    private int $stock = 0;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2, options: ['default' => 0])]
    private float $prix = 0.0;

    #[ORM\ManyToOne(inversedBy: 'tomes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Manga $manga = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumeroTome(): ?int
    {
        return $this->numero_tome;
    }

    public function setNumeroTome(int $numero_tome): static
    {
        $this->numero_tome = $numero_tome;

        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(int $stock): static
    {
        $this->stock = $stock;

        return $this;
    }

    public function getPrix(): float
    {
        return (float) $this->prix;
    }

    public function setPrix(float $prix): static
    {
        $this->prix = $prix;
        return $this;
    }

    public function getManga(): ?Manga
    {
        return $this->manga;
    }

    public function setManga(?Manga $manga): static
    {
        $this->manga = $manga;

        return $this;
    }
}
