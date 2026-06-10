<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class MangaApiService
{
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
            "https://api.jikan.moe/v4/manga/$id"
        );

        return $response->toArray();
    }
}