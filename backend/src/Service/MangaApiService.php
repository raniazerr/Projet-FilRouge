<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class MangaApiService
{
    private const TIMEOUT = 5;

    public function __construct(
        private HttpClientInterface $client,
        private string $baseUrl
    ) {}

    public function getManga(int $id): array
    {
        $response = $this->client->request(
            'GET',
            "{$this->baseUrl}/manga/$id",
            ['timeout' => self::TIMEOUT]
        );

        return $response->toArray();
    }

    // Recherche par titre — GET {baseUrl}/manga?q=...&limit=10
    public function searchManga(string $query): array
    {
        $response = $this->client->request(
            'GET',
            "{$this->baseUrl}/manga",
            [
                'timeout' => self::TIMEOUT,
                'query' => [
                    'q' => $query,
                    'limit' => 10,
                ],
            ]
        );

        return $response->toArray();
    }
}
