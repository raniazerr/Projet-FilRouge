<?php

namespace App\Tests\Service;

use App\Service\MangaApiService;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class MangaApiServiceTest extends TestCase
{
    public function testGetMangaCallsCorrectEndpointAndReturnsArray(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')->willReturn([
            'data' => ['id' => 1, 'title' => 'One Piece'],
        ]);

        $client = $this->createMock(HttpClientInterface::class);
        $client->expects($this->once())
            ->method('request')
            ->with('GET', 'https://api.tenrai.org/v1/manga/1')
            ->willReturn($response);

        $service = new MangaApiService($client);
        $result = $service->getManga(1);

        $this->assertSame(['data' => ['id' => 1, 'title' => 'One Piece']], $result);
    }

    public function testSearchMangaSendsQueryParameters(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')->willReturn(['data' => []]);

        $client = $this->createMock(HttpClientInterface::class);
        $client->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'https://api.tenrai.org/v1/manga',
                [
                    'query' => [
                        'q' => 'naruto',
                        'limit' => 10,
                    ],
                ]
            )
            ->willReturn($response);

        $service = new MangaApiService($client);
        $result = $service->searchManga('naruto');

        $this->assertSame(['data' => []], $result);
    }

    public function testGetGenresExtractsGenreNames(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')->willReturn([
            'data' => [
                'genres' => [
                    ['name' => 'Action'],
                    ['name' => 'Aventure'],
                ],
            ],
        ]);

        $client = $this->createMock(HttpClientInterface::class);
        $client->method('request')->willReturn($response);

        $service = new MangaApiService($client);
        $genres = $service->getGenres(1);

        $this->assertSame(['Action', 'Aventure'], $genres);
    }

    public function testGetGenresReturnsEmptyArrayWhenNoGenresInResponse(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')->willReturn(['data' => []]);

        $client = $this->createMock(HttpClientInterface::class);
        $client->method('request')->willReturn($response);

        $service = new MangaApiService($client);
        $genres = $service->getGenres(999);

        $this->assertSame([], $genres);
    }
}
