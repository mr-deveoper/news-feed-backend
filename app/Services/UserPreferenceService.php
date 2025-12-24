<?php

namespace App\Services;

use App\Models\UserPreference;
use App\Repositories\UserPreferenceRepository;
use Illuminate\Support\Facades\Cache;

/**
 * User Preference Service
 *
 * Handles user preference management
 */
class UserPreferenceService
{
    public function __construct(
        private readonly UserPreferenceRepository $preferenceRepository
    ) {}

    /**
     * Get user preferences
     */
    public function getUserPreferences(int $userId): UserPreference
    {
        return $this->preferenceRepository->getOrCreateForUser($userId);
    }

    /**
     * Update user preferences
     *
     * @param  array<string, mixed>  $preferences
     */
    public function updateUserPreferences(int $userId, array $preferences): bool
    {
        // Clear cached personalized feed when preferences change
        Cache::forget("user_{$userId}_personalized_feed");

        return $this->preferenceRepository->updateForUser($userId, $preferences);
    }

    /**
     * Update preferred sources
     *
     * @param  array<int>  $sourceIds
     */
    public function updatePreferredSources(int $userId, array $sourceIds): bool
    {
        return $this->updateUserPreferences($userId, [
            'preferred_sources' => $sourceIds,
        ]);
    }

    /**
     * Update preferred categories
     *
     * @param  array<int>  $categoryIds
     */
    public function updatePreferredCategories(int $userId, array $categoryIds): bool
    {
        return $this->updateUserPreferences($userId, [
            'preferred_categories' => $categoryIds,
        ]);
    }

    /**
     * Update preferred authors
     *
     * @param  array<int>  $authorIds
     */
    public function updatePreferredAuthors(int $userId, array $authorIds): bool
    {
        return $this->updateUserPreferences($userId, [
            'preferred_authors' => $authorIds,
        ]);
    }
}
