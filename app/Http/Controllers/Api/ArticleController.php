<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        // Validate query parameters
        $validated = $request->validate([
            'keyword' => ['sometimes', 'string', 'max:255'],
            'from' => ['sometimes', 'date'],
            'to' => ['sometimes', 'date', 'after_or_equal:from'],
            'source_ids' => ['sometimes', 'array'],
            'source_ids.*' => ['integer', 'exists:sources,id'],
            'category_ids' => ['sometimes', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'author_ids' => ['sometimes', 'array'],
            'author_ids.*' => ['integer', 'exists:authors,id'],
            'sort_by' => ['sometimes', 'in:published_at,created_at,title'],
            'sort_order' => ['sometimes', 'in:asc,desc'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        $perPage = $request->get('per_page', 15);
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
     */
    public function personalizedFeed(Request $request): AnonymousResourceCollection
    {
        $perPage = $request->validate([
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ])['per_page'] ?? 15;

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
