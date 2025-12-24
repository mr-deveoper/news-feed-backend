<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use App\Models\Source;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed basic data
        $this->source = Source::factory()->create();
        $this->category = Category::factory()->create();
        $this->author = Author::factory()->create();
    }

    /**
     * Test can list articles
     */
    public function test_can_list_articles(): void
    {
        Article::factory(5)->create([
            'source_id' => $this->source->id,
            'author_id' => $this->author->id,
        ]);

        $response = $this->getJson('/api/articles');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'description',
                        'url',
                        'published_at',
                    ],
                ],
                'links',
                'meta',
            ]);
    }

    /**
     * Test can view single article
     */
    public function test_can_view_single_article(): void
    {
        $article = Article::factory()->create([
            'source_id' => $this->source->id,
            'author_id' => $this->author->id,
        ]);

        $response = $this->getJson("/api/articles/{$article->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'description',
                    'content',
                    'url',
                    'source',
                    'author',
                ],
            ]);
    }

    /**
     * Test can search articles by keyword
     */
    public function test_can_search_articles_by_keyword(): void
    {
        Article::factory()->create([
            'title' => 'Laravel Framework News',
            'source_id' => $this->source->id,
            'author_id' => $this->author->id,
        ]);

        Article::factory()->create([
            'title' => 'React Development',
            'source_id' => $this->source->id,
            'author_id' => $this->author->id,
        ]);

        $response = $this->getJson('/api/articles?keyword=Laravel');

        $response->assertStatus(200);
        $this->assertGreaterThan(0, count($response->json('data')));
    }

    /**
     * Test can filter articles by source
     */
    public function test_can_filter_articles_by_source(): void
    {
        $source1 = Source::factory()->create();
        $source2 = Source::factory()->create();

        Article::factory(3)->create(['source_id' => $source1->id, 'author_id' => $this->author->id]);
        Article::factory(2)->create(['source_id' => $source2->id, 'author_id' => $this->author->id]);

        $response = $this->getJson("/api/articles?source_ids[]={$source1->id}");

        $response->assertStatus(200);
        $this->assertEquals(3, count($response->json('data')));
    }

    /**
     * Test pagination works
     */
    public function test_pagination_works(): void
    {
        Article::factory(25)->create([
            'source_id' => $this->source->id,
            'author_id' => $this->author->id,
        ]);

        $response = $this->getJson('/api/articles?per_page=10');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'links',
                'meta' => [
                    'current_page',
                    'per_page',
                    'total',
                ],
            ]);

        $this->assertEquals(10, count($response->json('data')));
    }

    /**
     * Test authenticated user can get personalized feed
     */
    public function test_authenticated_user_can_get_personalized_feed(): void
    {
        $user = User::factory()->create();
        $user->preference()->create([
            'preferred_sources' => [$this->source->id],
            'preferred_categories' => [],
            'preferred_authors' => [],
        ]);

        Article::factory(5)->create([
            'source_id' => $this->source->id,
            'author_id' => $this->author->id,
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withToken($token)
            ->getJson('/api/articles/feed/personalized');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'description',
                    ],
                ],
            ]);
    }

    /**
     * Test unauthenticated user cannot access personalized feed
     */
    public function test_unauthenticated_user_cannot_access_personalized_feed(): void
    {
        $response = $this->getJson('/api/articles/feed/personalized');

        $response->assertStatus(401);
    }
}
