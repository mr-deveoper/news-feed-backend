<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserPreferenceRequest;
use App\Http\Resources\UserPreferenceResource;
use App\Services\UserPreferenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * User Preference Controller
 *
 * Handles user news feed preferences
 */
class UserPreferenceController extends Controller
{
    public function __construct(
        private readonly UserPreferenceService $preferenceService
    ) {}

    /**
     * Display the user's preferences
     */
    public function show(Request $request): UserPreferenceResource
    {
        $preference = $this->preferenceService->getUserPreferences($request->user()->id);

        return new UserPreferenceResource($preference);
    }

    /**
     * Update the user's preferences
     */
    public function update(UpdateUserPreferenceRequest $request): JsonResponse
    {
        try {
            $this->preferenceService->updateUserPreferences(
                $request->user()->id,
                $request->validated()
            );

            $preference = $this->preferenceService->getUserPreferences($request->user()->id);

            return response()->json([
                'message' => 'Preferences updated successfully.',
                'preference' => new UserPreferenceResource($preference),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update preferences.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update preferred sources only
     */
    public function updateSources(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'source_ids' => ['required', 'array'],
            'source_ids.*' => ['integer', 'exists:sources,id'],
        ]);

        try {
            $this->preferenceService->updatePreferredSources(
                $request->user()->id,
                $validated['source_ids']
            );

            return response()->json([
                'message' => 'Preferred sources updated successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update preferred sources.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update preferred categories only
     */
    public function updateCategories(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_ids' => ['required', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
        ]);

        try {
            $this->preferenceService->updatePreferredCategories(
                $request->user()->id,
                $validated['category_ids']
            );

            return response()->json([
                'message' => 'Preferred categories updated successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update preferred categories.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update preferred authors only
     */
    public function updateAuthors(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'author_ids' => ['required', 'array'],
            'author_ids.*' => ['integer', 'exists:authors,id'],
        ]);

        try {
            $this->preferenceService->updatePreferredAuthors(
                $request->user()->id,
                $validated['author_ids']
            );

            return response()->json([
                'message' => 'Preferred authors updated successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update preferred authors.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
