<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use App\Models\Source;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('Seeding sources...');
        $this->seedSources();

        $this->command->info('Seeding categories...');
        $this->seedCategories();

        $this->command->info('Seeding users...');
        $this->seedUsers();

        $this->command->info('Seeding test articles...');
        $this->seedTestArticles();

        $this->command->info('Database seeded successfully!');
    }

    /**
     * Seed news sources
     */
    private function seedSources(): void
    {
        $sources = [
            [
                'name' => 'NewsAPI',
                'slug' => 'newsapi',
                'api_identifier' => 'newsapi',
                'url' => 'https://newsapi.org',
                'description' => 'News from over 70,000 sources',
                'is_active' => true,
            ],
            [
                'name' => 'The Guardian',
                'slug' => 'the-guardian',
                'api_identifier' => 'the-guardian',
                'url' => 'https://www.theguardian.com',
                'description' => 'Latest news from The Guardian',
                'is_active' => true,
            ],
            [
                'name' => 'New York Times',
                'slug' => 'nytimes',
                'api_identifier' => 'nytimes',
                'url' => 'https://www.nytimes.com',
                'description' => 'All the news that\'s fit to print',
                'is_active' => true,
            ],
            [
                'name' => 'BBC News',
                'slug' => 'bbc-news',
                'api_identifier' => 'bbc-news',
                'url' => 'https://www.bbc.com/news',
                'description' => 'BBC News - trusted news source',
                'is_active' => true,
            ],
            [
                'name' => 'OpenNews',
                'slug' => 'opennews',
                'api_identifier' => 'opennews',
                'url' => 'https://www.opennews.org',
                'description' => 'Open news from various sources',
                'is_active' => true,
            ],
        ];

        foreach ($sources as $source) {
            Source::create($source);
        }
    }

    /**
     * Seed categories
     */
    private function seedCategories(): void
    {
        $categories = [
            'Technology', 'Politics', 'Sports', 'Business', 'Entertainment',
            'Science', 'Health', 'World', 'Environment', 'Education',
        ];

        foreach ($categories as $category) {
            Category::create([
                'name' => $category,
                'slug' => Str::slug($category),
                'description' => "News about {$category}",
                'is_active' => true,
            ]);
        }
    }

    /**
     * Seed test users
     */
    private function seedUsers(): void
    {
        // Create test user
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Create user preference
        $user->preference()->create([
            'preferred_sources' => [1, 2],
            'preferred_categories' => [1, 2, 3],
            'preferred_authors' => [],
        ]);

        // Create additional users
        User::factory(5)->create()->each(function ($user) {
            $user->preference()->create([
                'preferred_sources' => [],
                'preferred_categories' => [],
                'preferred_authors' => [],
            ]);
        });
    }

    /**
     * Seed test articles
     */
    private function seedTestArticles(): void
    {
        $sources = Source::all();
        $categories = Category::all();

        // Create authors
        $authors = Author::factory(10)->create();

        // Create articles for each source
        foreach ($sources as $source) {
            Article::factory(20)->create([
                'source_id' => $source->id,
                'author_id' => $authors->random()->id,
            ])->each(function ($article) use ($categories) {
                // Attach random categories
                $article->categories()->attach(
                    $categories->random(rand(1, 3))->pluck('id')->toArray()
                );
            });
        }
    }
}
