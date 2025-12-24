<?php

namespace App\Services\NewsApi;

use App\Contracts\NewsApiClientInterface;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

/**
 * New York Times API Client
 *
 * Fetches articles from The New York Times
 * API Documentation: https://developer.nytimes.com/docs/articlesearch-product/1/overview
 */
class NyTimesApiClient implements NewsApiClientInterface
{
    private const BASE_URL = 'https://api.nytimes.com/svc/search/v2/';

    public function __construct(
        private readonly Client $httpClient,
        private readonly string $apiKey
    ) {}

    /**
     * Fetch articles from NY Times
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function fetchArticles(array $params = []): array
    {
        try {
            $response = $this->httpClient->get(self::BASE_URL.'articlesearch.json', [
                'query' => array_merge([
                    'api-key' => $this->apiKey,
                    'sort' => 'newest',
                ], $params),
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if ($data['status'] === 'OK') {
                return $this->normalizeArticles($data['response']['docs'] ?? []);
            }

            return [];
        } catch (\Exception $e) {
            Log::error('NY Times API fetch error: '.$e->getMessage());

            return [];
        }
    }

    /**
     * Get the source identifier
     */
    public function getSourceIdentifier(): string
    {
        return 'nytimes';
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
            $multimedia = $article['multimedia'] ?? [];
            $imageUrl = null;

            if (! empty($multimedia)) {
                $imageUrl = 'https://www.nytimes.com/'.$multimedia[0]['url'];
            }

            $byline = $article['byline']['original'] ?? 'New York Times';

            return [
                'title' => $article['headline']['main'] ?? '',
                'description' => $article['abstract'] ?? '',
                'content' => $article['lead_paragraph'] ?? '',
                'url' => $article['web_url'] ?? '',
                'image_url' => $imageUrl,
                'published_at' => $article['pub_date'] ?? null,
                'author_name' => $byline,
                'source_name' => 'New York Times',
                'category' => $article['section_name'] ?? 'General',
            ];
        }, $articles);
    }
}
