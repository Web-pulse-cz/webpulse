<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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

    public function parseTranslation(Request $request, array $translation, string $locale, $allowSlug = true): array
    {
        $translateAutomatically = $request->has('translateAutomatically') && $request->get('translateAutomatically') == true;

        if ($allowSlug) {
            if (in_array($translation['name'], ['', null]) && isset($request->translations['cs']['name'])) {
                $translation['name'] = $request->translations['cs']['name'];
            }
            $translation['slug'] = Str::slug($translation['name']);
        }

        if ($locale != 'cs') {
            foreach ($translation as $key => $value) {
                if ((in_array($value, ['', null]) && isset($request->translations['cs'][$key])) || $translateAutomatically) {
                    $value = $request->translations['cs'][$key];
                    $translation[$key] = $value;
                }

                if ($translateAutomatically) {
                    $value = $this->translate($value, $locale);
                    $translation[$key] = $value;
                }
            }
        }

        return $translation;
    }
}
