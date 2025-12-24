<?php

namespace App\Contracts;

/**
 * News API Client Interface
 *
 * Defines the contract for all news API implementations
 */
interface NewsApiClientInterface
{
    /**
     * Fetch articles from the news API
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function fetchArticles(array $params = []): array;

    /**
     * Get the source identifier for this API
     */
    public function getSourceIdentifier(): string;
}
