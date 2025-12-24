<?php

namespace App\Services\NewsApi;

use App\Contracts\NewsApiClientInterface;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

/**
 * BBC News API Client
 *
 * Fetches articles from BBC News via NewsAPI
 * Note: BBC News is available through NewsAPI.org
 */
class BbcNewsApiClient implements NewsApiClientInterface
{
    private const BASE_URL = 'https://newsapi.org/v2/';

    public function __construct(
        private readonly Client $httpClient,
        private readonly string $apiKey
    ) {}

    /**
     * Fetch articles from BBC News
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function fetchArticles(array $params = []): array
    {
        try {
            $response = $this->httpClient->get(self::BASE_URL.'everything', [
                'query' => array_merge([
                    'apiKey' => $this->apiKey,
                    'sources' => 'bbc-news',
                    'language' => 'en',
                    'pageSize' => 100,
                    'sortBy' => 'publishedAt',
                ], $params),
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if ($data['status'] === 'ok') {
                return $this->normalizeArticles($data['articles'] ?? []);
            }

            return [];
        } catch (\Exception $e) {
            Log::error('BBC News API fetch error: '.$e->getMessage());

            return [];
        }
    }

    /**
     * Get the source identifier
     */
    public function getSourceIdentifier(): string
    {
        return 'bbc-news';
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
                'author_name' => $article['author'] ?? 'BBC News',
                'source_name' => 'BBC News',
                'category' => 'General',
            ];
        }, $articles);
    }
}
