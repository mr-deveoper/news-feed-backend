<?php

namespace App\Repositories;

use App\Models\Author;
use Illuminate\Database\Eloquent\Model;

/**
 * Author Repository Implementation
 */
class AuthorRepository extends BaseRepository
{
    protected function getModel(): Model
    {
        return new Author;
    }

    /**
     * Find or create author by name and email
     */
    public function findOrCreateByNameAndEmail(string $name, ?string $email = null): Author
    {
        $query = $this->model->newQuery()->where('name', $name);

        if ($email) {
            $query->where('email', $email);
        }

        $author = $query->first();

        if (! $author) {
            $author = $this->model->newQuery()->create([
                'name' => $name,
                'email' => $email,
            ]);
        }

        return $author;
    }
}
