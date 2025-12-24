<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SourceResource;
use App\Repositories\SourceRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Source Controller
 *
 * Handles news source listing and details
 */
class SourceController extends Controller
{
    public function __construct(
        private readonly SourceRepository $sourceRepository
    ) {}

    /**
     * Display a listing of sources
     */
    public function index(): AnonymousResourceCollection
    {
        $sources = $this->sourceRepository->getActive();

        return SourceResource::collection($sources);
    }

    /**
     * Display the specified source
     */
    public function show(int $id): SourceResource|JsonResponse
    {
        try {
            $source = $this->sourceRepository->findOrFail($id);

            return new SourceResource($source);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Source not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve source.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     * (Not implemented - sources are managed by admins)
     */
    public function store()
    {
        return response()->json([
            'message' => 'Method not allowed.',
        ], 405);
    }

    /**
     * Update the specified resource in storage.
     * (Not implemented - sources are managed by admins)
     */
    public function update($id)
    {
        return response()->json([
            'message' => 'Method not allowed.',
        ], 405);
    }

    /**
     * Remove the specified resource from storage.
     * (Not implemented - sources are managed by admins)
     */
    public function destroy($id)
    {
        return response()->json([
            'message' => 'Method not allowed.',
        ], 405);
    }
}
