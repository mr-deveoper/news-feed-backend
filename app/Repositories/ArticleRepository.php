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
        return $this->executeSearchQuery($filters, $perPage);
    }

    /**
     * Execute the search query using EloquentFilter
     */
    private function executeSearchQuery(array $filters, int $perPage): LengthAwarePaginator
    {
        // Prepare filters for EloquentFilter
        // Remove per_page from filters as it's handled separately
        $filterParams = $filters;
        unset($filterParams['per_page']);

        // Apply filter using EloquentFilter
        $query = $this->model->newQuery()
            ->with([
                'source' => function ($q) {
                    $q->select('id', 'name', 'slug', 'created_at', 'updated_at');
                },
                'author' => function ($q) {
                    $q->select('id', 'name', 'email', 'created_at', 'updated_at');
                },
            ])
            ->filter($filterParams);

        // Apply default sorting if no sort_by or sort_order provided
        $sortBy = $filters['sort_by'] ?? 'published_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';

        // Only apply if not already sorted by the filter
        if (empty($filters['sort_by']) && empty($filters['sort_order'])) {
            $query->orderBy($sortBy, $sortOrder);
        }

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

            $query = $this->model->newQuery()
                ->with([
                    'source' => function ($q) {
                        $q->select('id', 'name', 'slug', 'created_at', 'updated_at');
                    },
                    'author' => function ($q) {
                        $q->select('id', 'name', 'email', 'created_at', 'updated_at');
                    },
                    'categories' => function ($q) {
                        $q->select('categories.id', 'categories.name', 'categories.slug', 'categories.created_at', 'categories.updated_at');
                    },
                ]);

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
                ->with([
                    'author' => function ($q) {
                        $q->select('id', 'name', 'email', 'created_at', 'updated_at');
                    },
                    'categories' => function ($q) {
                        $q->select('categories.id', 'categories.name', 'categories.slug', 'categories.created_at', 'categories.updated_at');
                    },
                ])
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
                ->with([
                    'source' => function ($q) {
                        $q->select('id', 'name', 'slug', 'created_at', 'updated_at');
                    },
                    'author' => function ($q) {
                        $q->select('id', 'name', 'email', 'created_at', 'updated_at');
                    },
                    'categories' => function ($q) {
                        $q->select('categories.id', 'categories.name', 'categories.slug', 'categories.created_at', 'categories.updated_at');
                    },
                ])
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
                ->with([
                    'source' => function ($q) {
                        $q->select('id', 'name', 'slug', 'created_at', 'updated_at');
                    },
                    'categories' => function ($q) {
                        $q->select('categories.id', 'categories.name', 'categories.slug', 'categories.created_at', 'categories.updated_at');
                    },
                ])
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
