<?php

namespace App\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Article Repository Interface
 *
 * Defines the contract for article-specific repository operations
 */
interface ArticleRepositoryInterface extends RepositoryInterface
{
    /**
     * Search and filter articles with pagination
     *
     * @param  array<string, mixed>  $filters
     */
    public function searchAndFilter(array $filters, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get personalized feed for a user
     */
    public function getPersonalizedFeed(int $userId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get articles by source
     */
    public function getBySource(int $sourceId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get articles by category
     */
    public function getByCategory(int $categoryId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get articles by author
     */
    public function getByAuthor(int $authorId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Check if article with URL exists
     */
    public function existsByUrl(string $url): bool;
}
