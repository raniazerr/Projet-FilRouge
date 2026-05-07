<?php

namespace App\Entity;

use App\Repository\ReservationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $date_reservation = null;

    #[ORM\Column(length: 50)]
    private ?string $statut = null;

    #[ORM\Column]
    private ?int $id_utilisateur = null;

    #[ORM\Column]
    private ?int $id_manga = null;

    /**
     * @var Collection<int, Manga>
     */
    #[ORM\OneToMany(targetEntity: Manga::class, mappedBy: 'reservation')]
    private Collection $manga;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $utilisateur = null;

    public function __construct()
    {
        $this->manga = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateReservation(): ?\DateTimeImmutable
    {
        return $this->date_reservation;
    }

    public function setDateReservation(\DateTimeImmutable $date_reservation): static
    {
        $this->date_reservation = $date_reservation;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getIdUtilisateur(): ?int
    {
        return $this->id_utilisateur;
    }

    public function setIdUtilisateur(int $id_utilisateur): static
    {
        $this->id_utilisateur = $id_utilisateur;

        return $this;
    }

    public function getIdManga(): ?int
    {
        return $this->id_manga;
    }

    public function setIdManga(int $id_manga): static
    {
        $this->id_manga = $id_manga;

        return $this;
    }

    /**
     * @return Collection<int, Manga>
     */
    public function getManga(): Collection
    {
        return $this->manga;
    }

    public function addManga(Manga $manga): static
    {
        if (!$this->manga->contains($manga)) {
            $this->manga->add($manga);
            $manga->setReservation($this);
        }

        return $this;
    }

    public function removeManga(Manga $manga): static
    {
        if ($this->manga->removeElement($manga)) {
            // set the owning side to null (unless already changed)
            if ($manga->getReservation() === $this) {
                $manga->setReservation(null);
            }
        }

        return $this;
    }

    public function getUtilisateur(): ?User
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?User $utilisateur): static
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }
}
