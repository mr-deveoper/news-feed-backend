<?php

namespace App\Services;

use App\Models\Article;
use App\Repositories\ArticleRepository;
use App\Repositories\AuthorRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\SourceRepository;
use App\Services\NewsApi\NewsApiFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * News Aggregator Service
 *
 * Aggregates news from multiple sources and saves to database
 */
class NewsAggregatorService
{
    public function __construct(
        private readonly ArticleRepository $articleRepository,
        private readonly SourceRepository $sourceRepository,
        private readonly AuthorRepository $authorRepository,
        private readonly CategoryRepository $categoryRepository
    ) {}

    /**
     * Fetch and save articles from all news sources
     *
     * @return array<string, int>
     */
    public function aggregateNews(): array
    {
        $stats = [
            'total_fetched' => 0,
            'total_saved' => 0,
            'total_skipped' => 0,
            'errors' => 0,
        ];

        $clients = NewsApiFactory::createAll();

        foreach ($clients as $client) {
            try {
                $articles = $client->fetchArticles();
                $stats['total_fetched'] += count($articles);

                $source = $this->sourceRepository->findByApiIdentifier($client->getSourceIdentifier());

                if (! $source) {
                    Log::warning("Source not found: {$client->getSourceIdentifier()}");

                    continue;
                }

                foreach ($articles as $articleData) {
                    try {
                        $result = $this->saveArticle($articleData, $source->id);

                        if ($result) {
                            $stats['total_saved']++;
                        } else {
                            $stats['total_skipped']++;
                        }
                    } catch (\Exception $e) {
                        $stats['errors']++;
                        Log::error('Error saving article: '.$e->getMessage(), [
                            'article' => $articleData,
                        ]);
                    }
                }
            } catch (\Exception $e) {
                $stats['errors']++;
                Log::error('Error fetching from '.$client->getSourceIdentifier().': '.$e->getMessage());
            }
        }

        return $stats;
    }

    /**
     * Save article to database
     *
     * @param  array<string, mixed>  $data
     */
    private function saveArticle(array $data, int $sourceId): bool
    {
        // Skip if article already exists
        if ($this->articleRepository->existsByUrl($data['url'])) {
            return false;
        }

        return DB::transaction(function () use ($data, $sourceId) {
            // Find or create author
            $author = $this->authorRepository->findOrCreateByNameAndEmail(
                $data['author_name'] ?? 'Unknown'
            );

            // Create article
            $article = Article::create([
                'title' => $data['title'],
                'slug' => Str::slug($data['title']).'-'.Str::random(8),
                'description' => $data['description'],
                'content' => $data['content'],
                'url' => $data['url'],
                'image_url' => $data['image_url'],
                'source_id' => $sourceId,
                'author_id' => $author->id,
                'published_at' => $data['published_at'] ?? now(),
            ]);

            // Attach categories if provided
            if (! empty($data['category'])) {
                $category = $this->categoryRepository->findBySlug(Str::slug($data['category']));

                if ($category) {
                    $article->categories()->attach($category->id);
                }
            }

            return true;
        });
    }
}
