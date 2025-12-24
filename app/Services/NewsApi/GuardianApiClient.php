<?php

namespace App\Services\NewsApi;

use App\Contracts\NewsApiClientInterface;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

/**
 * The Guardian API Client
 *
 * Fetches articles from The Guardian
 * API Documentation: https://open-platform.theguardian.com/documentation/
 */
class GuardianApiClient implements NewsApiClientInterface
{
    private const BASE_URL = 'https://content.guardianapis.com/';

    public function __construct(
        private readonly Client $httpClient,
        private readonly string $apiKey
    ) {}

    /**
     * Fetch articles from The Guardian
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function fetchArticles(array $params = []): array
    {
        try {
            $response = $this->httpClient->get(self::BASE_URL.'search', [
                'query' => array_merge([
                    'api-key' => $this->apiKey,
                    'page-size' => 50,
                    'show-fields' => 'thumbnail,trailText,body,byline',
                    'order-by' => 'newest',
                ], $params),
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if ($data['response']['status'] === 'ok') {
                return $this->normalizeArticles($data['response']['results'] ?? []);
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Guardian API fetch error: '.$e->getMessage());

            return [];
        }
    }

    /**
     * Get the source identifier
     */
    public function getSourceIdentifier(): string
    {
        return 'the-guardian';
    }

    /**
     * Normalize articles to common format
     *
     * @param  array<int, array<string, mixed>>  $articles
     * @return array<int, array<string, mixed>>
     */
    private function normalizeArticles(array $articles): array
    {
        return array_map(function ($article) {
            $fields = $article['fields'] ?? [];

            return [
                'title' => $article['webTitle'] ?? '',
                'description' => $fields['trailText'] ?? '',
                'content' => $fields['body'] ?? '',
                'url' => $article['webUrl'] ?? '',
                'image_url' => $fields['thumbnail'] ?? null,
                'published_at' => $article['webPublicationDate'] ?? null,
                'author_name' => $fields['byline'] ?? 'The Guardian',
                'source_name' => 'The Guardian',
                'category' => $article['sectionName'] ?? 'General',
            ];
        }, $articles);
    }
}
