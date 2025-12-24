<?php

namespace App\Services\NewsApi;

use App\Contracts\NewsApiClientInterface;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

/**
 * NewsAPI.org Client
 *
 * Fetches articles from NewsAPI.org
 * API Documentation: https://newsapi.org/docs
 */
class NewsApiClient implements NewsApiClientInterface
{
    private const BASE_URL = 'https://newsapi.org/v2/';

    public function __construct(
        private readonly Client $httpClient,
        private readonly string $apiKey
    ) {}

    /**
     * Fetch articles from NewsAPI
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function fetchArticles(array $params = []): array
    {
        try {
            // Use top-headlines endpoint for variety of articles from various sources
            $response = $this->httpClient->get(self::BASE_URL.'top-headlines', [
                'query' => array_merge([
                    'apiKey' => $this->apiKey,
                    'language' => 'en',
                    'pageSize' => 100,
                    'country' => 'us',
                ], $params),
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if ($data['status'] === 'ok') {
                return $this->normalizeArticles($data['articles'] ?? []);
            }

            return [];
        } catch (\Exception $e) {
            Log::error('NewsAPI fetch error: '.$e->getMessage());

            return [];
        }
    }

    /**
     * Get the source identifier
     */
    public function getSourceIdentifier(): string
    {
        return 'newsapi';
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
            return [
                'title' => $article['title'] ?? '',
                'description' => $article['description'] ?? '',
                'content' => $article['content'] ?? '',
                'url' => $article['url'] ?? '',
                'image_url' => $article['urlToImage'] ?? null,
                'published_at' => $article['publishedAt'] ?? null,
                'author_name' => $article['author'] ?? 'NewsAPI',
                'source_name' => $article['source']['name'] ?? 'NewsAPI',
                'category' => 'General',
            ];
        }, $articles);
    }
}
