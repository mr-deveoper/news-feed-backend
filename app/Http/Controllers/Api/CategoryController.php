<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Repositories\CategoryRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Category Controller
 *
 * Handles category listing and details
 */
class CategoryController extends Controller
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository
    ) {}

    /**
     * Display a listing of categories
     */
    public function index(): AnonymousResourceCollection
    {
        $categories = $this->categoryRepository->getActive();

        return CategoryResource::collection($categories);
    }

    /**
     * Display the specified category
     */
    public function show(int $id): CategoryResource|JsonResponse
    {
        try {
            $category = $this->categoryRepository->findOrFail($id);

            return new CategoryResource($category);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Category not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve category.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     * (Not implemented - categories are managed by admins)
     */
    public function store()
    {
        return response()->json([
            'message' => 'Method not allowed.',
        ], 405);
    }

    /**
     * Update the specified resource in storage.
     * (Not implemented - categories are managed by admins)
     */
    public function update($id)
    {
        return response()->json([
            'message' => 'Method not allowed.',
        ], 405);
    }

    /**
     * Remove the specified resource from storage.
     * (Not implemented - categories are managed by admins)
     */
    public function destroy($id)
    {
        return response()->json([
            'message' => 'Method not allowed.',
        ], 405);
    }
}
