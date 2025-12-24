<?php

namespace App\Repositories;

use App\Models\UserPreference;
use Illuminate\Database\Eloquent\Model;

/**
 * UserPreference Repository Implementation
 */
class UserPreferenceRepository extends BaseRepository
{
    protected function getModel(): Model
    {
        return new UserPreference;
    }

    /**
     * Get or create user preference
     */
    public function getOrCreateForUser(int $userId): UserPreference
    {
        return $this->model->newQuery()->firstOrCreate(
            ['user_id' => $userId],
            [
                'preferred_sources' => [],
                'preferred_categories' => [],
                'preferred_authors' => [],
            ]
        );
    }

    /**
     * Update user preferences
     *
     * @param  array<string, mixed>  $preferences
     */
    public function updateForUser(int $userId, array $preferences): bool
    {
        $userPreference = $this->getOrCreateForUser($userId);

        return $userPreference->update($preferences);
    }
}
