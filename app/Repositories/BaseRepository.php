<?php

namespace App\Repositories;

use App\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Base Repository Implementation
 *
 * Provides common repository functionality for all repositories
 */
abstract class BaseRepository implements RepositoryInterface
{
    protected Model $model;

    /**
     * BaseRepository constructor
     */
    public function __construct()
    {
        $this->model = $this->getModel();
    }

    /**
     * Get the model instance
     */
    abstract protected function getModel(): Model;

    /**
     * Get all records
     *
     * @param  array<string>  $columns
     * @return Collection<int, Model>
     */
    public function all(array $columns = ['*']): Collection
    {
        return $this->model->newQuery()->get($columns);
    }

    /**
     * Get paginated records
     *
     * @param  array<string>  $columns
     * @return LengthAwarePaginator<Model>
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->model->newQuery()->paginate($perPage, $columns);
    }

    /**
     * Find a record by ID
     */
    public function find(int $id): ?Model
    {
        return $this->model->newQuery()->find($id);
    }

    /**
     * Find a record by ID or fail
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail(int $id): Model
    {
        return $this->model->newQuery()->findOrFail($id);
    }

    /**
     * Find a record by column
     */
    public function findBy(string $column, mixed $value): ?Model
    {
        return $this->model->newQuery()->where($column, $value)->first();
    }

    /**
     * Create a new record
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Model
    {
        return $this->model->newQuery()->create($data);
    }

    /**
     * Update a record
     *
     * @param  array<string, mixed>  $data
     */
    public function update(int $id, array $data): bool
    {
        $record = $this->findOrFail($id);

        return $record->update($data);
    }

    /**
     * Delete a record
     */
    public function delete(int $id): bool
    {
        $record = $this->findOrFail($id);

        return $record->delete();
    }

    /**
     * Get records matching criteria
     *
     * @param  array<string, mixed>  $criteria
     * @return Collection<int, Model>
     */
    public function findWhere(array $criteria): Collection
    {
        $query = $this->model->newQuery();

        foreach ($criteria as $column => $value) {
            $query->where($column, $value);
        }

        return $query->get();
    }
}
