<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Repository\ReservationRepository;
use App\Repository\TomeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/reservations')]
class ReservationController extends AbstractController
{
    // GET /api/reservations — voir son panier
    #[Route('', methods: ['GET'])]
    public function index(ReservationRepository $repo): JsonResponse
    {
        $user = $this->getUser();
        $reservations = $repo->findBy(['utilisateur' => $user]);

        return new JsonResponse(array_map([$this, 'normalize'], $reservations));
    }

    // POST /api/reservations — ajouter au panier
    #[Route('', methods: ['POST'])]
    public function create(Request $request, TomeRepository $tomeRepo, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $tome = $tomeRepo->find($data['tome_id'] ?? null);
        if (!$tome) {
            return new JsonResponse(['error' => 'Tome introuvable'], Response::HTTP_BAD_REQUEST);
        }

        if ($tome->getStock() <= 0) {
            return new JsonResponse(['error' => 'Tome en rupture de stock'], Response::HTTP_BAD_REQUEST);
        }

        $reservation = new Reservation();
        $reservation->setTome($tome);
        $reservation->setUtilisateur($this->getUser());
        $reservation->setDateReservation(new \DateTimeImmutable());
        $reservation->setStatut('active');

        $em->persist($reservation);
        $em->flush();

        return new JsonResponse($this->normalize($reservation), Response::HTTP_CREATED);
    }

    // DELETE /api/reservations/{id} — supprimer
    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(Reservation $reservation, EntityManagerInterface $em): JsonResponse
    {
        if ($reservation->getUtilisateur() !== $this->getUser()) {
            return new JsonResponse(['error' => 'Accès refusé'], Response::HTTP_FORBIDDEN);
        }

        $em->remove($reservation);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    // PATCH /api/reservations/{id}/valider — valider
    #[Route('/{id}/valider', methods: ['PATCH'])]
    public function valider(Reservation $reservation, EntityManagerInterface $em): JsonResponse
    {
        if ($reservation->getUtilisateur() !== $this->getUser()) {
            return new JsonResponse(['error' => 'Accès refusé'], Response::HTTP_FORBIDDEN);
        }

        $tome = $reservation->getTome();
        if ($tome->getStock() <= 0) {
            return new JsonResponse(['error' => 'Tome en rupture de stock'], Response::HTTP_BAD_REQUEST);
        }

        $tome->setStock($tome->getStock() - 1);
        $reservation->setStatut('validée');
        $em->flush();

        return new JsonResponse($this->normalize($reservation));
    }
    
    // GET /api/reservations/admin — voir toutes les réservations (admin)
    // #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin', methods: ['GET'])]
    public function adminIndex(ReservationRepository $repo): JsonResponse
    {
        $reservations = $repo->findAll();
        return new JsonResponse(array_map([$this, 'normalize'], $reservations));
    }

    // PATCH /api/reservations/admin/{id}/statut — clôturer ou annuler (admin)
    // #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/{id}/statut', methods: ['PATCH'])]
    public function adminUpdateStatut(Reservation $reservation, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $statut = $data['statut'] ?? null;

        if (!in_array($statut, ['validée', 'clôturée', 'annulée'])) {
            return new JsonResponse(['error' => 'Statut invalide'], Response::HTTP_BAD_REQUEST);
        }

        $reservation->setStatut($statut);
        $em->flush();

        return new JsonResponse($this->normalize($reservation));
    }

    private function normalize(Reservation $r): array
    {
        return [
            'id' => $r->getId(),
            'statut' => $r->getStatut(),
            'date_reservation' => $r->getDateReservation()?->format('Y-m-d H:i:s'),
            'tome' => [
                'id' => $r->getTome()->getId(),
                'numero_tome' => $r->getTome()->getNumeroTome(),
                'prix' => $r->getTome()->getPrix(),
                'stock' => $r->getTome()->getStock(),
                'manga_titre' => $r->getTome()->getManga()?->getTitre(),
                'manga_image' => $r->getTome()->getManga()?->getImage(),
            ]
        ];
    }
}