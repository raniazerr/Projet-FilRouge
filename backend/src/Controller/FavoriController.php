<?php

namespace App\Controller;

use App\Entity\Favori;
use App\Repository\FavoriRepository;
use App\Repository\MangaRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/favori')]
final class FavoriController extends AbstractController
{
    #[Route(name: 'app_favori_index', methods: ['GET'])]
    public function index(FavoriRepository $favoriRepository): JsonResponse
    {
        $favoris = array_map([$this, 'normalizeFavori'], $favoriRepository->findAll());

        return new JsonResponse($favoris);
    }

    #[Route('/new', name: 'app_favori_new', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository, MangaRepository $mangaRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return new JsonResponse(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }

        $user = $userRepository->find($data['utilisateur'] ?? null);
        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur introuvable'], Response::HTTP_BAD_REQUEST);
        }

        $manga = $mangaRepository->find($data['manga'] ?? null);
        if (!$manga) {
            return new JsonResponse(['error' => 'Manga introuvable'], Response::HTTP_BAD_REQUEST);
        }

        $favori = new Favori();
        $favori->setUtilisateur($user);
        $favori->setManga($manga);

        $entityManager->persist($favori);
        $entityManager->flush();

        return new JsonResponse($this->normalizeFavori($favori), Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'app_favori_show', methods: ['GET'])]
    public function show(?Favori $favori): JsonResponse
    {
        if (!$favori) {
            return new JsonResponse(['error' => 'Favori not found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($this->normalizeFavori($favori));
    }

    #[Route('/{id}/edit', name: 'app_favori_edit', methods: ['PUT', 'PATCH'])]
    public function edit(Request $request, ?Favori $favori, EntityManagerInterface $entityManager, UserRepository $userRepository, MangaRepository $mangaRepository): JsonResponse
    {
        if (!$favori) {
            return new JsonResponse(['error' => 'Favori not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return new JsonResponse(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }

        if (isset($data['utilisateur'])) {
            $user = $userRepository->find($data['utilisateur']);
            if (!$user) {
                return new JsonResponse(['error' => 'Utilisateur introuvable'], Response::HTTP_BAD_REQUEST);
            }
            $favori->setUtilisateur($user);
        }

        if (isset($data['manga'])) {
            $manga = $mangaRepository->find($data['manga']);
            if (!$manga) {
                return new JsonResponse(['error' => 'Manga introuvable'], Response::HTTP_BAD_REQUEST);
            }
            $favori->setManga($manga);
        }

        $entityManager->flush();

        return new JsonResponse($this->normalizeFavori($favori));
    }

    #[Route('/{id}', name: 'app_favori_delete', methods: ['DELETE'])]
    public function delete(?Favori $favori, EntityManagerInterface $entityManager): JsonResponse
    {
        if (!$favori) {
            return new JsonResponse(['error' => 'Favori not found'], Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($favori);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function normalizeFavori(Favori $favori): array
    {
        return [
            'id' => $favori->getId(),
            'utilisateur' => $favori->getUtilisateur()?->getId(),
            'manga' => $favori->getManga()?->getId(),
        ];
    }
}
