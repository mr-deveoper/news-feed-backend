<?php

namespace App\ModelFilters;

use EloquentFilter\ModelFilter;

/**
 * Article Filter
 *
 * Handles filtering for Article model using EloquentFilter package
 */
class ArticleFilter extends ModelFilter
{
    /**
     * Filter by keyword (searches in title, description, content)
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function keyword(string $keyword)
    {
        // Use fulltext search for MySQL, fallback to LIKE for others
        if (config('database.default') === 'mysql') {
            return $this->where(function ($q) use ($keyword) {
                $q->whereFullText(['title', 'description', 'content'], $keyword)
                    ->orWhere('title', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%");
            });
        }

        // Fallback search using LIKE for SQLite and other databases
        return $this->where(function ($q) use ($keyword) {
            $q->where('title', 'like', "%{$keyword}%")
                ->orWhere('description', 'like', "%{$keyword}%")
                ->orWhere('content', 'like', "%{$keyword}%");
        });
    }

    /**
     * Filter by start date (from)
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function from(string $from)
    {
        return $this->where('published_at', '>=', $from);
    }

    /**
     * Filter by end date (to)
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function to(string $to)
    {
        return $this->where('published_at', '<=', $to);
    }

    /**
     * Filter by source IDs
     *
     * @param  array<int>  $sourceIds
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function sourceIds(array $sourceIds)
    {
        return $this->whereIn('source_id', $sourceIds);
    }

    /**
     * Filter by category IDs
     *
     * @param  array<int>  $categoryIds
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function categoryIds(array $categoryIds)
    {
        return $this->whereHas('categories', function ($q) use ($categoryIds) {
            $q->whereIn('categories.id', $categoryIds);
        });
    }

    /**
     * Filter by author IDs
     *
     * @param  array<int>  $authorIds
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function authorIds(array $authorIds)
    {
        return $this->whereIn('author_id', $authorIds);
    }

    /**
     * Sort by field
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function sortBy(string $sortBy)
    {
        $sortOrder = $this->input('sort_order', 'desc');

        return $this->orderBy($sortBy, $sortOrder);
    }
}
