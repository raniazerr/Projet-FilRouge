<?php

namespace App\Entity;

use App\Repository\MangaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MangaRepository::class)]
class Manga
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $api_id = null;

    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[ORM\Column(length: 500)]
    private ?string $image = null;

    /**
     * @var Collection<int, Tome>
     */
    #[ORM\OneToMany(targetEntity: Tome::class, mappedBy: 'manga', orphanRemoval: true)]
    private Collection $tomes;

    /**
     * @var Collection<int, Favori>
     */
    #[ORM\OneToMany(targetEntity: Favori::class, mappedBy: 'manga')]
    private Collection $favoris;

    public function __construct()
    {
        $this->tomes = new ArrayCollection();
        $this->favoris = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getApiId(): ?int { return $this->api_id; }
    public function setApiId(int $api_id): static { $this->api_id = $api_id; return $this; }

    public function getTitre(): ?string { return $this->titre; }
    public function setTitre(string $titre): static { $this->titre = $titre; return $this; }

    public function getImage(): ?string { return $this->image; }
    public function setImage(string $image): static { $this->image = $image; return $this; }

    public function getTomes(): Collection { return $this->tomes; }

    public function addTome(Tome $tome): static
    {
        if (!$this->tomes->contains($tome)) {
            $this->tomes->add($tome);
            $tome->setManga($this);
        }
        return $this;
    }

    public function removeTome(Tome $tome): static
    {
        if ($this->tomes->removeElement($tome)) {
            if ($tome->getManga() === $this) {
                $tome->setManga(null);
            }
        }
        return $this;
    }

    public function getFavoris(): Collection { return $this->favoris; }

    public function addFavori(Favori $favori): static
    {
        if (!$this->favoris->contains($favori)) {
            $this->favoris->add($favori);
            $favori->setManga($this);
        }
        return $this;
    }

    public function removeFavori(Favori $favori): static
    {
        if ($this->favoris->removeElement($favori)) {
            if ($favori->getManga() === $this) {
                $favori->setManga(null);
            }
        }
        return $this;
    }
}