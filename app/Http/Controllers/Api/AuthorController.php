<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AuthorResource;
use App\Repositories\AuthorRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Author Controller
 *
 * Handles author listing and details
 */
class AuthorController extends Controller
{
    public function __construct(
        private readonly AuthorRepository $authorRepository
    ) {}

    /**
     * Display a listing of authors
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        $perPage = $validated['per_page'] ?? 15;
        $authors = $this->authorRepository->paginate($perPage);

        return AuthorResource::collection($authors);
    }

    /**
     * Display the specified author
     */
    public function show(int $id): AuthorResource|JsonResponse
    {
        try {
            $author = $this->authorRepository->findOrFail($id);

            return new AuthorResource($author);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Author not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve author.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     * (Not implemented - authors are auto-created from news sources)
     */
    public function store()
    {
        return response()->json([
            'message' => 'Method not allowed.',
        ], 405);
    }

    /**
     * Update the specified resource in storage.
     * (Not implemented - authors are managed automatically)
     */
    public function update($id)
    {
        return response()->json([
            'message' => 'Method not allowed.',
        ], 405);
    }

    /**
     * Remove the specified resource from storage.
     * (Not implemented - authors are managed automatically)
     */
    public function destroy($id)
    {
        return response()->json([
            'message' => 'Method not allowed.',
        ], 405);
    }
}
