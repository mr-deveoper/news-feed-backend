<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Article>
 */
class ArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence();

        return [
            'title' => $title,
            'slug' => \Illuminate\Support\Str::slug($title).'-'.\Illuminate\Support\Str::random(8),
            'description' => fake()->paragraph(),
            'content' => fake()->paragraphs(5, true),
            'url' => fake()->unique()->url(),
            'image_url' => fake()->imageUrl(800, 600, 'news'),
            'source_id' => \App\Models\Source::factory(),
            'author_id' => \App\Models\Author::factory(),
            'published_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
