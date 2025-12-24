<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

/**
 * Article Model
 *
 * Represents a news article from various sources
 *
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string|null $description
 * @property string|null $content
 * @property string $url
 * @property string|null $image_url
 * @property int $source_id
 * @property int|null $author_id
 * @property \Carbon\Carbon|null $published_at
 */
class Article extends Model
{
    /** @use HasFactory<\Database\Factories\ArticleFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'slug',
        'description',
        'content',
        'url',
        'image_url',
        'source_id',
        'author_id',
        'published_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($article) {
            if (empty($article->slug)) {
                $article->slug = Str::slug($article->title).'-'.Str::random(8);
            }
        });
    }

    /**
     * Get the source that owns the article.
     *
     * @return BelongsTo<Source, $this>
     */
    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }

    /**
     * Get the author that owns the article.
     *
     * @return BelongsTo<Author, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    /**
     * Get the categories for the article.
     *
     * @return BelongsToMany<Category, $this>
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    /**
     * Scope a query to search articles by keyword.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Article>  $query
     * @return \Illuminate\Database\Eloquent\Builder<Article>
     */
    public function scopeSearch($query, ?string $keyword)
    {
        if (empty($keyword)) {
            return $query;
        }

        // Use fulltext search for MySQL, fallback to LIKE for others (SQLite in tests)
        if (config('database.default') === 'mysql') {
            return $query->where(function ($q) use ($keyword) {
                $q->whereFullText(['title', 'description', 'content'], $keyword)
                    ->orWhere('title', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%");
            });
        }

        // Fallback search using LIKE for SQLite and other databases
        return $query->where(function ($q) use ($keyword) {
            $q->where('title', 'like', "%{$keyword}%")
                ->orWhere('description', 'like', "%{$keyword}%")
                ->orWhere('content', 'like', "%{$keyword}%");
        });
    }

    /**
     * Scope a query to filter by date range.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Article>  $query
     * @return \Illuminate\Database\Eloquent\Builder<Article>
     */
    public function scopeDateRange($query, ?string $from, ?string $to)
    {
        if ($from) {
            $query->where('published_at', '>=', $from);
        }

        if ($to) {
            $query->where('published_at', '<=', $to);
        }

        return $query;
    }

    /**
     * Scope a query to filter by source.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Article>  $query
     * @param  array<int>|null  $sourceIds
     * @return \Illuminate\Database\Eloquent\Builder<Article>
     */
    public function scopeBySource($query, ?array $sourceIds)
    {
        if (empty($sourceIds)) {
            return $query;
        }

        return $query->whereIn('source_id', $sourceIds);
    }

    /**
     * Scope a query to filter by category.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Article>  $query
     * @param  array<int>|null  $categoryIds
     * @return \Illuminate\Database\Eloquent\Builder<Article>
     */
    public function scopeByCategory($query, ?array $categoryIds)
    {
        if (empty($categoryIds)) {
            return $query;
        }

        return $query->whereHas('categories', function ($q) use ($categoryIds) {
            $q->whereIn('categories.id', $categoryIds);
        });
    }

    /**
     * Scope a query to filter by author.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Article>  $query
     * @param  array<int>|null  $authorIds
     * @return \Illuminate\Database\Eloquent\Builder<Article>
     */
    public function scopeByAuthor($query, ?array $authorIds)
    {
        if (empty($authorIds)) {
            return $query;
        }

        return $query->whereIn('author_id', $authorIds);
    }
}
