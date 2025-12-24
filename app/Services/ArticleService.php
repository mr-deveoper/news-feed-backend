<?php

namespace App\Services;

use App\Repositories\ArticleRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Article Service
 *
 * Handles article business logic
 */
class ArticleService
{
    public function __construct(
        private readonly ArticleRepository $articleRepository
    ) {}

    /**
     * Get articles with filters and pagination
     *
     * @param  array<string, mixed>  $filters
     */
    public function getArticles(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->articleRepository->searchAndFilter($filters, $perPage);
    }

    /**
     * Get article by ID
     *
     * @return \App\Models\Article
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getArticleById(int $id)
    {
        return $this->articleRepository->findOrFail($id);
    }

    /**
     * Get personalized feed for user
     */
    public function getPersonalizedFeed(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->articleRepository->getPersonalizedFeed($userId, $perPage);
    }

    /**
     * Get articles by source
     */
    public function getArticlesBySource(int $sourceId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->articleRepository->getBySource($sourceId, $perPage);
    }

    /**
     * Get articles by category
     */
    public function getArticlesByCategory(int $categoryId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->articleRepository->getByCategory($categoryId, $perPage);
    }

    /**
     * Get articles by author
     */
    public function getArticlesByAuthor(int $authorId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->articleRepository->getByAuthor($authorId, $perPage);
    }
}
