<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Base Repository Interface
 *
 * Defines the contract for all repository implementations
 */
interface RepositoryInterface
{
    /**
     * Get all records
     *
     * @param  array<string>  $columns
     * @return Collection<int, Model>
     */
    public function all(array $columns = ['*']): Collection;

    /**
     * Get paginated records
     *
     * @param  array<string>  $columns
     * @return LengthAwarePaginator<Model>
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator;

    /**
     * Find a record by ID
     */
    public function find(int $id): ?Model;

    /**
     * Find a record by ID or fail
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail(int $id): Model;

    /**
     * Find a record by column
     */
    public function findBy(string $column, mixed $value): ?Model;

    /**
     * Create a new record
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Model;

    /**
     * Update a record
     *
     * @param  array<string, mixed>  $data
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete a record
     */
    public function delete(int $id): bool;

    /**
     * Get records matching criteria
     *
     * @param  array<string, mixed>  $criteria
     * @return Collection<int, Model>
     */
    public function findWhere(array $criteria): Collection;
}
