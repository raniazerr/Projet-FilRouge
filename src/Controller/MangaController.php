<?php

namespace App\Controller;

use App\Entity\Manga;
use App\Repository\MangaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/manga')]
final class MangaController extends AbstractController
{
    #[Route('/index', name: 'app_manga_index', methods: ['GET'])]
    public function index(MangaRepository $mangaRepository): JsonResponse
    {
        $mangas = array_map([$this, 'normalizeManga'], $mangaRepository->findAll());
        return new JsonResponse($mangas);
    }

    #[Route('/new', name: 'app_manga_new', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return new JsonResponse(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }

        if (empty($data['api_id'] ?? null) || empty($data['titre'] ?? '') || !isset($data['image'])) {
            return new JsonResponse(['error' => 'Les champs api_id, titre et image sont requis'], Response::HTTP_BAD_REQUEST);
        }

        $manga = new Manga();
        $manga->setApiId((int) $data['api_id']);
        $manga->setTitre((string) $data['titre']);
        $manga->setImage((string) $data['image']);
        $manga->setStock((int) ($data['stock'] ?? 0));      
        $manga->setPrix((float) ($data['prix'] ?? 0.0));    

        $entityManager->persist($manga);
        $entityManager->flush();

        return new JsonResponse($this->normalizeManga($manga), Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'app_manga_show', methods: ['GET'])]
    public function show(?Manga $manga): JsonResponse
    {
        if (!$manga) {
            return new JsonResponse(['error' => 'Manga not found'], Response::HTTP_NOT_FOUND);
        }
        return new JsonResponse($this->normalizeManga($manga));
    }

    #[Route('/{id}/edit', name: 'app_manga_edit', methods: ['PUT', 'PATCH'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, ?Manga $manga, EntityManagerInterface $entityManager): JsonResponse
    {
        if (!$manga) {
            return new JsonResponse(['error' => 'Manga not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return new JsonResponse(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }

        if (isset($data['api_id']))  $manga->setApiId((int) $data['api_id']);
        if (isset($data['titre']))   $manga->setTitre((string) $data['titre']);
        if (isset($data['image']))   $manga->setImage((string) $data['image']);
        if (isset($data['stock']))   $manga->setStock((int) $data['stock']);      
        if (isset($data['prix']))    $manga->setPrix((float) $data['prix']);      

        $entityManager->flush();

        return new JsonResponse($this->normalizeManga($manga));
    }

    #[Route('/{id}', name: 'app_manga_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(?Manga $manga, EntityManagerInterface $entityManager): JsonResponse
    {
        if (!$manga) {
            return new JsonResponse(['error' => 'Manga not found'], Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($manga);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function normalizeManga(Manga $manga): array
    {
        return [
            'id'     => $manga->getId(),
            'api_id' => $manga->getApiId(),
            'titre'  => $manga->getTitre(),
            'image'  => $manga->getImage(),
            'stock'  => $manga->getStock(),   
            'prix'   => $manga->getPrix(),    
        ];
    }
}