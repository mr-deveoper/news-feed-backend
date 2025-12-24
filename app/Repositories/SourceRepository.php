<?php

namespace App\Repositories;

use App\Models\Source;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Source Repository Implementation
 */
class SourceRepository extends BaseRepository
{
    protected function getModel(): Model
    {
        return new Source;
    }

    /**
     * Get all active sources
     *
     * @return Collection<int, Source>
     */
    public function getActive(): Collection
    {
        return Cache::remember('active_sources', 3600, function () {
            return $this->model->newQuery()->active()->get();
        });
    }

    /**
     * Find source by API identifier
     */
    public function findByApiIdentifier(string $apiIdentifier): ?Source
    {
        return $this->model->newQuery()->where('api_identifier', $apiIdentifier)->first();
    }
}
