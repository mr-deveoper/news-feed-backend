<?php

namespace App\Services\NewsApi;

use App\Contracts\NewsApiClientInterface;
use GuzzleHttp\Client;

/**
 * News API Factory
 *
 * Creates news API client instances based on configuration
 */
class NewsApiFactory
{
    /**
     * Create a news API client
     */
    public static function create(string $source): ?NewsApiClientInterface
    {
        $httpClient = new Client(['timeout' => 30]);

        return match ($source) {
            'newsapi' => new NewsApiClient(
                $httpClient,
                config('services.newsapi.api_key')
            ),
            'the-guardian' => new GuardianApiClient(
                $httpClient,
                config('services.guardian.api_key')
            ),
            'nytimes' => new NyTimesApiClient(
                $httpClient,
                config('services.nytimes.api_key')
            ),
            'bbc-news' => new BbcNewsApiClient(
                $httpClient,
                config('services.newsapi.api_key') // BBC uses NewsAPI
            ),
            default => null,
        };
    }

    /**
     * Get all configured news API clients
     *
     * @return array<NewsApiClientInterface>
     */
    public static function createAll(): array
    {
        $clients = [];

        foreach (['newsapi', 'the-guardian', 'nytimes', 'bbc-news'] as $source) {
            $client = self::create($source);

            if ($client) {
                $clients[] = $client;
            }
        }

        return $clients;
    }
}
