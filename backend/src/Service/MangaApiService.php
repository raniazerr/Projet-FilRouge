<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class MangaApiService
{
    public function __construct(
        private HttpClientInterface $client
    ) {}

    public function getSynopsis(int $apiId): ?string
    {
    $data = $this->getManga($apiId);
    return $data['data']['synopsis'] ?? null;
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