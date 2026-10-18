<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class MangaApiService
{
    // Service d'intégration avec l'API externe Tenrai.
    // Il sert à rechercher des mangas et à récupérer leurs détails
    // afin de les importer dans le catalogue local.

    public function __construct(
        private HttpClientInterface $client
    ) {}

    public function getGenres(int $apiId): array
    {
        $data = $this->getManga($apiId);
        return array_map(fn($g) => $g['name'], $data['data']['genres'] ?? []);
    }

    public function getManga(int $id): array
    {
        $response = $this->client->request(
            'GET',
            "https://api.tenrai.org/v1/manga/{$id}",
            <?php

namespace App\Controller;

use App\Entity\Favori;
use App\Repository\FavoriRepository;
use App\Repository\MangaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/favori')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final class FavoriController extends AbstractController
{
    // Contrôleur de gestion des favoris.
    // Il permet de lister, créer, afficher et supprimer des favoris associés à un utilisateur.

    #[Route(name: 'app_favori_index', methods: ['GET'])]
    public function index(FavoriRepository $favoriRepository): JsonResponse
    {
        $favoris = array_map([$this, 'normalizeFavori'], $favoriRepository->findBy(['utilisateur' => $this->getUser()]));

        return new JsonResponse($favoris);
    }
   
    #[Route('/mes-favoris', name: 'app_favori_mes_favoris', methods: ['GET'])]
    public function mesFavoris(FavoriRepository $favoriRepository): JsonResponse
    {
        $user = $this->getUser();
        $favoris = $favoriRepository->findBy(['utilisateur' => $user]);

        $result = array_map(fn($f) => [
            'favoriId' => $f->getId(),
            'manga' => [
                'id' => $f->getManga()->getId(),
                'titre' => $f->getManga()->getTitre(),
                'image' => $f->getManga()->getImage(),
            ],
        ], $favoris);

        return new JsonResponse($result);
    }

    #[Route('/new', name: 'app_favori_new', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, MangaRepository $mangaRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return new JsonResponse(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->getUser();

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
        if (!$favori || $favori->getUtilisateur() !== $this->getUser()) {
            return new JsonResponse(['error' => 'Favori not found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($this->normalizeFavori($favori));
    }

    #[Route('/{id}/edit', name: 'app_favori_edit', methods: ['PUT', 'PATCH'])]
    public function edit(Request $request, ?Favori $favori, EntityManagerInterface $entityManager, MangaRepository $mangaRepository): JsonResponse
    {
        if (!$favori || $favori->getUtilisateur() !== $this->getUser()) {
            return new JsonResponse(['error' => 'Favori not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return new JsonResponse(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
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
        if (!$favori || $favori->getUtilisateur() !== $this->getUser()) {
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
        );

        return $response->toArray();
    }

    // Recherche par titre
    public function searchManga(string $query): array
    {
        $response = $this->client->request(
            'GET',
            'https://api.tenrai.org/v1/manga',
            [
                'query' => [
                    'q' => $query,
                    'limit' => 10,
                ],
            ]
        );

        return $response->toArray();
    }
}