<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Repository\ReservationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/reservation')]
final class ReservationController extends AbstractController
{
    #[Route(name: 'app_reservation_index', methods: ['GET'])]
    public function index(ReservationRepository $reservationRepository): JsonResponse
    {
        $reservations = array_map(
            [$this, 'normalizeReservation'],
            $reservationRepository->findAll()
        );

        return new JsonResponse($reservations);
    }

    #[Route(name: 'app_reservation_new', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return new JsonResponse(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }

        $user = $userRepository->find($data['utilisateur'] ?? null);
        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur introuvable'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $date = new \DateTimeImmutable($data['date_reservation'] ?? '');
        } catch (\Exception $exception) {
            return new JsonResponse(['error' => 'Date invalide'], Response::HTTP_BAD_REQUEST);
        }

        $reservation = new Reservation();
        $reservation->setDateReservation($date);
        $reservation->setStatut($data['statut'] ?? '');
        $reservation->setIdManga((int) ($data['id_manga'] ?? 0));
        $reservation->setUtilisateur($user);

        $entityManager->persist($reservation);
        $entityManager->flush();

        return new JsonResponse($this->normalizeReservation($reservation), Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'app_reservation_show', methods: ['GET'])]
    public function show(?Reservation $reservation): JsonResponse
    {
        if (!$reservation) {
            return new JsonResponse(['error' => 'Reservation not found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($this->normalizeReservation($reservation));
    }

    #[Route('/{id}', name: 'app_reservation_edit', methods: ['PUT', 'PATCH'])]
    public function edit(Request $request, ?Reservation $reservation, EntityManagerInterface $entityManager, UserRepository $userRepository): JsonResponse
    {
        if (!$reservation) {
            return new JsonResponse(['error' => 'Reservation not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return new JsonResponse(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }

        if (isset($data['date_reservation'])) {
            try {
                $reservation->setDateReservation(new \DateTimeImmutable($data['date_reservation']));
            } catch (\Exception $exception) {
                return new JsonResponse(['error' => 'Date invalide'], Response::HTTP_BAD_REQUEST);
            }
        }

        if (isset($data['statut'])) {
            $reservation->setStatut($data['statut']);
        }

        if (isset($data['id_manga'])) {
            $reservation->setIdManga((int) $data['id_manga']);
        }

        if (isset($data['utilisateur'])) {
            $user = $userRepository->find($data['utilisateur']);
            if (!$user) {
                return new JsonResponse(['error' => 'Utilisateur introuvable'], Response::HTTP_BAD_REQUEST);
            }
            $reservation->setUtilisateur($user);
        }

        $entityManager->flush();

        return new JsonResponse($this->normalizeReservation($reservation));
    }

    #[Route('/{id}', name: 'app_reservation_delete', methods: ['DELETE'])]
    public function delete(?Reservation $reservation, EntityManagerInterface $entityManager): JsonResponse
    {
        if (!$reservation) {
            return new JsonResponse(['error' => 'Reservation not found'], Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($reservation);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function normalizeReservation(Reservation $reservation): array
    {
        return [
            'id' => $reservation->getId(),
            'date_reservation' => $reservation->getDateReservation()?->format('Y-m-d H:i:s'),
            'statut' => $reservation->getStatut(),
            'id_manga' => $reservation->getIdManga(),
            'utilisateur' => $reservation->getUtilisateur()?->getId(),
        ];
    }
}
