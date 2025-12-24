<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Article\PersonalizedFeedRequest;
use App\Http\Requests\Article\SearchArticleRequest;
use App\Http\Resources\ArticleResource;
use App\Services\ArticleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Article Controller
 *
 * Handles article listing, searching, filtering, and personalized feeds
 */
class ArticleController extends Controller
{
    public function __construct(
        private readonly ArticleService $articleService
    ) {}

    /**
     * Display a listing of articles with search and filters
     *
     * Supports filtering by:
     * - Keyword (searches title, description, content)
     * - Date range (from/to dates in Y-m-d format)
     * - Sources (array of source IDs)
     * - Categories (array of category IDs)
     * - Authors (array of author IDs)
     * - Sorting (by published_at, created_at, or title)
     * - Pagination (per_page: 1-100, default: 15)
     */
    public function index(SearchArticleRequest $request): AnonymousResourceCollection
    {
        $validated = $request->validated();
        $perPage = $validated['per_page'] ?? 15;
        $articles = $this->articleService->getArticles($validated, $perPage);

        return ArticleResource::collection($articles);
    }

    /**
     * Display the specified article
     */
    public function show(int $id): ArticleResource|JsonResponse
    {
        try {
            $article = $this->articleService->getArticleById($id);
            $article->load(['source', 'author', 'categories']);

            return new ArticleResource($article);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Article not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve article.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get personalized news feed for authenticated user
     *
     * Returns articles filtered by user's preferred sources, categories, and authors.
     * Users can customize their preferences via the /api/preferences endpoints.
     */
    public function personalizedFeed(PersonalizedFeedRequest $request): AnonymousResourceCollection
    {
        $validated = $request->validated();
        $perPage = $validated['per_page'] ?? 15;

        $articles = $this->articleService->getPersonalizedFeed($request->user()->id, $perPage);

        return ArticleResource::collection($articles);
    }

    /**
     * Store a newly created resource in storage.
     * (Not implemented - articles are created via scheduled commands)
     */
    public function store(Request $request)
    {
        return response()->json([
            'message' => 'Articles are automatically aggregated from news sources.',
        ], 405);
    }

    /**
     * Update the specified resource in storage.
     * (Not implemented - articles are read-only)
     */
    public function update(Request $request, string $id)
    {
        return response()->json([
            'message' => 'Articles cannot be modified.',
        ], 405);
    }

    /**
     * Remove the specified resource from storage.
     * (Not implemented - articles are read-only)
     */
    public function destroy(string $id)
    {
        return response()->json([
            'message' => 'Articles cannot be deleted.',
        ], 405);
    }
}
