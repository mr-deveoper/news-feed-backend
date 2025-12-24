<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Category;
use App\Models\Source;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPreferenceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->user->preference()->create([
            'preferred_sources' => [],
            'preferred_categories' => [],
            'preferred_authors' => [],
        ]);
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    /**
     * Test user can view their preferences
     */
    public function test_user_can_view_preferences(): void
    {
        $response = $this->withToken($this->token)
            ->getJson('/api/preferences');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'user_id',
                    'preferred_sources',
                    'preferred_categories',
                    'preferred_authors',
                ],
            ]);
    }

    /**
     * Test user can update all preferences
     */
    public function test_user_can_update_all_preferences(): void
    {
        $source = Source::factory()->create();
        $category = Category::factory()->create();
        $author = Author::factory()->create();

        $response = $this->withToken($this->token)
            ->putJson('/api/preferences', [
                'preferred_sources' => [$source->id],
                'preferred_categories' => [$category->id],
                'preferred_authors' => [$author->id],
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Preferences updated successfully.',
            ]);

        $this->assertDatabaseHas('user_preferences', [
            'user_id' => $this->user->id,
        ]);
    }

    /**
     * Test user can update preferred sources only
     */
    public function test_user_can_update_preferred_sources(): void
    {
        $source = Source::factory()->create();

        $response = $this->withToken($this->token)
            ->putJson('/api/preferences/sources', [
                'source_ids' => [$source->id],
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Preferred sources updated successfully.',
            ]);
    }

    /**
     * Test user can update preferred categories only
     */
    public function test_user_can_update_preferred_categories(): void
    {
        $category = Category::factory()->create();

        $response = $this->withToken($this->token)
            ->putJson('/api/preferences/categories', [
                'category_ids' => [$category->id],
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Preferred categories updated successfully.',
            ]);
    }

    /**
     * Test user can update preferred authors only
     */
    public function test_user_can_update_preferred_authors(): void
    {
        $author = Author::factory()->create();

        $response = $this->withToken($this->token)
            ->putJson('/api/preferences/authors', [
                'author_ids' => [$author->id],
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Preferred authors updated successfully.',
            ]);
    }

    /**
     * Test preference update validates source IDs
     */
    public function test_preference_update_validates_source_ids(): void
    {
        $response = $this->withToken($this->token)
            ->putJson('/api/preferences/sources', [
                'source_ids' => [99999], // Non-existent source
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['source_ids.0']);
    }

    /**
     * Test unauthenticated user cannot access preferences
     */
    public function test_unauthenticated_user_cannot_access_preferences(): void
    {
        $response = $this->getJson('/api/preferences');

        $response->assertStatus(401);
    }
}
