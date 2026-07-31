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
            "https://api.tenrai.org/v1/manga/{$id}"
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