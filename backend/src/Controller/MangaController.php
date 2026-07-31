<?php

namespace App\Controller;

use App\Entity\Manga;
use App\Repository\MangaRepository;
use App\Service\MangaApiService;
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
    // Contrôleur principal des mangas.
    // Il gère l'affichage de la liste, la recherche externe via l'API,
    // l'ajout au catalogue local et la récupération du détail d'un manga.

   #[Route('/index', name: 'app_manga_index', methods: ['GET'])]
public function index(Request $request, MangaRepository $mangaRepository): JsonResponse
{
    $tri = $request->query->get('tri');

    $mangasEntities = $tri === 'popularite'
        ? $mangaRepository->findAllSortedByPopularity()
        : $mangaRepository->findAll();

    $mangas = array_map([$this, 'normalizeManga'], $mangasEntities);
    $topMangas = array_map([$this, 'normalizeManga'], $mangaRepository->findMostReserved(3));

    return new JsonResponse(['mangas' => $mangas, 'top' => $topMangas]);
}

    // GET /manga/search?q=... — chercher un manga sur l'API externe (admin)
#[Route('/search', name: 'app_manga_search', methods: ['GET'])]
#[IsGranted('ROLE_ADMIN')]
public function search(Request $request, MangaApiService $api): JsonResponse
{
    $query = $request->query->get('q', '');

    if (strlen(trim($query)) < 2) {
        return new JsonResponse(['error' => 'Requête trop courte (2 caractères min)'], Response::HTTP_BAD_REQUEST);
    }

    $data = $api->searchManga($query);

    $resultats = array_map(function ($m) {
        return [
            'api_id' => $m['mal_id'],
            'titre' => $m['title'],
            'image' => $m['images']['jpg']['image_url'] ?? null,
            'synopsis' => $m['synopsis'] ?? null,
            'volumes' => $m['volumes'] ?? null,
            'statut' => $m['status'] ?? null,
        ];
    }, $data['data'] ?? []);

    return new JsonResponse($resultats);
}

    #[Route('/new', name: 'app_manga_new', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function create(Request $request, EntityManagerInterface $entityManager, MangaApiService $api): JsonResponse 
    {
    $data = json_decode($request->getContent(), true);
    // $response = $api->getManga((int)$data['api_id']);
    // var_dump($response);
    // die();


    if (!isset($data['api_id'])) {
        return new JsonResponse(['error' => 'api_id required'], 400);
    }

    $apiData = $api->getManga((int)$data['api_id'])['data'];

    $manga = new Manga();
    $manga->setApiId($apiData['mal_id']);
    $manga->setTitre($apiData['title']);
    $manga->setImage($apiData['images']['jpg']['image_url']);
    $manga->setSynopsis($apiData['synopsis']);
    $manga->setGenres(array_map(fn($g) => $g['name'], $apiData['genres'] ?? []));

    $entityManager->persist($manga);
    $entityManager->flush();

    return new JsonResponse($this->normalizeManga($manga), 201);
}

   #[Route('/{id}', name: 'app_manga_show', methods: ['GET'])]
public function show(
    ?Manga $manga,
    MangaApiService $api
): JsonResponse {
    if (!$manga) {
        return new JsonResponse(['error' => 'Manga not found'], 404);
    }

    $apiData = $api->getManga($manga->getApiId());

    // Récupère les tomes et leur stock
    $tomes = array_map(function($tome) {
        return [
            'id'          => $tome->getId(),
            'numero_tome' => $tome->getNumeroTome(),
            'stock'       => $tome->getStock(),
            'prix'        => $tome->getPrix(),
        ];
    }, $manga->getTomes()->toArray());

    return new JsonResponse([
        'id'          => $manga->getId(),
        'api_id'      => $manga->getApiId(),
        'titre'       => $apiData['data']['title'] ?? $manga->getTitre(),
        'description' => $apiData['data']['synopsis'] ?? null,
        'image'       => $apiData['data']['images']['jpg']['image_url'] ?? $manga->getImage(),
        'auteurs'     => array_map(fn($a) => $a['name'], $apiData['data']['authors'] ?? []),
        'volumes'     => $apiData['data']['volumes'] ?? null,
        'statut'      => $apiData['data']['status'] ?? null,
        'tomes'       => $tomes, // ← liste des tomes en stock
    ]);
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
            'synopsis' => $manga->getSynopsis(), 
            'genres' => $manga->getGenres() ?? [],
        ];
    }
}