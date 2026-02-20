<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class GoogleTranslatorService
{
    protected Client $client;
    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = env('GOOGLE_TRANSLATE_API_KEY');

        // Inicializace Guzzle klienta s V2 endpointem
        $this->client = new Client([
            'base_uri' => 'https://translation.googleapis.com/language/translate/v2',
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);
    }

    public function translate(string $text, string $targetLanguage, string $sourceLanguage = 'cs'): string
    {
        try {
            $response = $this->client->post('', [
                'query' => [
                    'key' => $this->apiKey
                ],
                'json' => [
                    'q' => $text,
                    'source' => $sourceLanguage,
                    'target' => $targetLanguage,
                    'format' => 'html'
                ]
            ]);

            $body = json_decode($response->getBody()->getContents(), true);

            return $body['data']['translations'][0]['translatedText'] ?? $text;

        } catch (\Exception $e) {
            Log::error('Google Translate HTTP Error: ' . $e->getMessage());
            return $text;
        }
    }
}
