<?php

namespace App\Repositories;

use App\Contracts\ArticleRepositoryInterface;
use App\Models\Article;
use App\Models\UserPreference;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Article Repository Implementation
 *
 * Handles all article data access with caching support
 */
class ArticleRepository extends BaseRepository implements ArticleRepositoryInterface
{
    /**
     * Cache TTL in seconds (1 hour)
     */
    private const CACHE_TTL = 3600;

    /**
     * Get the model instance
     */
    protected function getModel(): Model
    {
        return new Article;
    }

    /**
     * Search and filter articles with pagination
     *
     * @param  array<string, mixed>  $filters
     */
    public function searchAndFilter(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery()->with(['source', 'author', 'categories']);

        // Search by keyword
        if (! empty($filters['keyword'])) {
            $query->search($filters['keyword']);
        }

        // Filter by date range
        if (! empty($filters['from']) || ! empty($filters['to'])) {
            $query->dateRange($filters['from'] ?? null, $filters['to'] ?? null);
        }

        // Filter by source
        if (! empty($filters['source_ids'])) {
            $query->bySource($filters['source_ids']);
        }

        // Filter by category
        if (! empty($filters['category_ids'])) {
            $query->byCategory($filters['category_ids']);
        }

        // Filter by author
        if (! empty($filters['author_ids'])) {
            $query->byAuthor($filters['author_ids']);
        }

        // Sort by
        $sortBy = $filters['sort_by'] ?? 'published_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($perPage);
    }

    /**
     * Get personalized feed for a user
     */
    public function getPersonalizedFeed(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        $cacheKey = "user_{$userId}_personalized_feed_page_".request('page', 1);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($userId, $perPage) {
            $preference = UserPreference::where('user_id', $userId)->first();

            $query = $this->model->newQuery()->with(['source', 'author', 'categories']);

            if ($preference) {
                // Filter by preferred sources
                if (! empty($preference->preferred_sources)) {
                    $query->whereIn('source_id', $preference->preferred_sources);
                }

                // Filter by preferred categories
                if (! empty($preference->preferred_categories)) {
                    $query->whereHas('categories', function ($q) use ($preference) {
                        $q->whereIn('categories.id', $preference->preferred_categories);
                    });
                }

                // Filter by preferred authors
                if (! empty($preference->preferred_authors)) {
                    $query->whereIn('author_id', $preference->preferred_authors);
                }
            }

            return $query->orderBy('published_at', 'desc')->paginate($perPage);
        });
    }

    /**
     * Get articles by source
     */
    public function getBySource(int $sourceId, int $perPage = 15): LengthAwarePaginator
    {
        $cacheKey = "source_{$sourceId}_articles_page_".request('page', 1);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($sourceId, $perPage) {
            return $this->model->newQuery()
                ->with(['author', 'categories'])
                ->where('source_id', $sourceId)
                ->orderBy('published_at', 'desc')
                ->paginate($perPage);
        });
    }

    /**
     * Get articles by category
     */
    public function getByCategory(int $categoryId, int $perPage = 15): LengthAwarePaginator
    {
        $cacheKey = "category_{$categoryId}_articles_page_".request('page', 1);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($categoryId, $perPage) {
            return $this->model->newQuery()
                ->with(['source', 'author', 'categories'])
                ->whereHas('categories', function ($q) use ($categoryId) {
                    $q->where('categories.id', $categoryId);
                })
                ->orderBy('published_at', 'desc')
                ->paginate($perPage);
        });
    }

    /**
     * Get articles by author
     */
    public function getByAuthor(int $authorId, int $perPage = 15): LengthAwarePaginator
    {
        $cacheKey = "author_{$authorId}_articles_page_".request('page', 1);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($authorId, $perPage) {
            return $this->model->newQuery()
                ->with(['source', 'categories'])
                ->where('author_id', $authorId)
                ->orderBy('published_at', 'desc')
                ->paginate($perPage);
        });
    }

    /**
     * Check if article with URL exists
     */
    public function existsByUrl(string $url): bool
    {
        return $this->model->newQuery()->where('url', $url)->exists();
    }

    /**
     * Clear user's personalized feed cache
     */
    public function clearUserFeedCache(int $userId): void
    {
        Cache::forget("user_{$userId}_personalized_feed");
    }
}
