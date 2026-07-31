<?php

namespace App\Controller;

use App\Entity\Tome;
use App\Repository\MangaRepository;
use App\Repository\TomeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/tome')]
final class TomeController extends AbstractController
{
    // Contrôleur de gestion des tomes.
    // Il sert à lister les tomes d'un manga, à les créer et à les modifier ou supprimer.

    // Tous les tomes d'un manga
    #[Route('/manga/{id}', name: 'app_tome_by_manga', methods: ['GET'])]
    public function byManga(int $id, TomeRepository $tomeRepository, MangaRepository $mangaRepository): JsonResponse
    {
        $manga = $mangaRepository->find($id);
        if (!$manga) {
            return new JsonResponse(['error' => 'Manga introuvable'], Response::HTTP_NOT_FOUND);
        }

        $tomes = array_map([$this, 'normalizeTome'], $tomeRepository->findBy(['manga' => $manga], ['numero_tome' => 'ASC']));
        return new JsonResponse($tomes);
    }

    // Détail d'un tome
    #[Route('/{id}', name: 'app_tome_show', methods: ['GET'])]
    public function show(?Tome $tome): JsonResponse
    {
        if (!$tome) {
            return new JsonResponse(['error' => 'Tome introuvable'], Response::HTTP_NOT_FOUND);
        }
        return new JsonResponse($this->normalizeTome($tome));
    }

    // Créer un tome (admin)
    #[Route('', name: 'app_tome_new', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function create(Request $request, EntityManagerInterface $entityManager, MangaRepository $mangaRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return new JsonResponse(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }

        $manga = $mangaRepository->find($data['manga_id'] ?? null);
        if (!$manga) {
            return new JsonResponse(['error' => 'Manga introuvable'], Response::HTTP_BAD_REQUEST);
        }

        if (!isset($data['numero_tome'])) {
            return new JsonResponse(['error' => 'Le champ numero_tome est requis'], Response::HTTP_BAD_REQUEST);
        }

        $tome = new Tome();
        $tome->setManga($manga);
        $tome->setNumeroTome((int) $data['numero_tome']);
        $tome->setStock((int) ($data['stock'] ?? 0));
        $tome->setPrix((float) ($data['prix'] ?? 0.0));

        $entityManager->persist($tome);
        $entityManager->flush();

        return new JsonResponse($this->normalizeTome($tome), Response::HTTP_CREATED);
    }

    // Modifier un tome (admin)
    #[Route('/{id}', name: 'app_tome_edit', methods: ['PUT', 'PATCH'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, ?Tome $tome, EntityManagerInterface $entityManager): JsonResponse
    {
        if (!$tome) {
            return new JsonResponse(['error' => 'Tome introuvable'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return new JsonResponse(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }

        if (isset($data['numero_tome'])) $tome->setNumeroTome((int) $data['numero_tome']);
        if (isset($data['stock']))       $tome->setStock((int) $data['stock']);
        if (isset($data['prix']))        $tome->setPrix((float) $data['prix']);

        $entityManager->flush();

        return new JsonResponse($this->normalizeTome($tome));
    }

    // Supprimer un tome (admin)
    #[Route('/{id}', name: 'app_tome_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(?Tome $tome, EntityManagerInterface $entityManager): JsonResponse
    {
        if (!$tome) {
            return new JsonResponse(['error' => 'Tome introuvable'], Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($tome);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function normalizeTome(Tome $tome): array
    {
        return [
            'id'          => $tome->getId(),
            'numero_tome' => $tome->getNumeroTome(),
            'stock'       => $tome->getStock(),
            'prix'        => $tome->getPrix(),
            'manga_id'    => $tome->getManga()?->getId(),
            'manga_titre' => $tome->getManga()?->getTitre(),
        ];
    }
}