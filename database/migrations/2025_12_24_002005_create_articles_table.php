<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->longText('content')->nullable();
            // Use VARCHAR(2000) for URLs to handle long URLs from news APIs
            // Note: Can't use unique() directly on VARCHAR(2000), will add unique index with prefix after table creation
            $table->string('url', 2000);
            $table->string('image_url', 2000)->nullable();
            $table->foreignId('source_id')->constrained()->onDelete('cascade');
            $table->foreignId('author_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index('slug');
            $table->index('published_at');
            $table->index('author_id');
            $table->index(['source_id', 'published_at']);
            $table->index(['author_id', 'published_at']);

            // Only add fulltext index for MySQL
            if (config('database.default') === 'mysql') {
                $table->fullText(['title', 'description', 'content']);
            }
        });

        // Add unique index on url with prefix (MySQL limitation for long VARCHAR)
        // Using first 191 characters for uniqueness check
        if (config('database.default') === 'mysql') {
            DB::statement('ALTER TABLE articles ADD UNIQUE INDEX articles_url_unique (url(191))');
            DB::statement('ALTER TABLE articles ADD INDEX articles_url_index (url(191))');
        } else {
            // For other databases, use regular unique
            Schema::table('articles', function (Blueprint $table) {
                $table->unique('url');
                $table->index('url');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
