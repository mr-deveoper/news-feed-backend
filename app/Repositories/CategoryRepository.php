<?php

namespace App\Repositories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Category Repository Implementation
 */
class CategoryRepository extends BaseRepository
{
    protected function getModel(): Model
    {
        return new Category;
    }

    /**
     * Get all active categories
     *
     * @return Collection<int, Category>
     */
    public function getActive(): Collection
    {
        return Cache::remember('active_categories', 3600, function () {
            return $this->model->newQuery()->active()->get();
        });
    }

    /**
     * Find category by slug
     */
    public function findBySlug(string $slug): ?Category
    {
        return $this->model->newQuery()->where('slug', $slug)->first();
    }
}
