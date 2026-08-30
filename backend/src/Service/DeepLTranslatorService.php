<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class DeepLTranslatorService
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $deeplApiKey
    ) {}

    public function translate(string $text, string $targetLang = 'FR'): string
    {
        if (empty($text)) {
            return $text;
        }

        $response = $this->httpClient->request('POST', 'https://api-free.deepl.com/v2/translate', [
            'headers' => [
                'Authorization' => 'DeepL-Auth-Key ' . $this->deeplApiKey,
            ],
            'body' => [
                'text' => $text,
                'target_lang' => $targetLang,
                'source_lang' => 'EN',
            ],
        ]);

        $data = $response->toArray();
        return $data['translations'][0]['text'] ?? $text;
    }
}