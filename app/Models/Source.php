<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Source Model
 *
 * Represents a news source (e.g., The Guardian, NewsAPI, etc.)
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $api_identifier
 * @property string|null $url
 * @property string|null $description
 * @property bool $is_active
 */
class Source extends Model
{
    /** @use HasFactory<\Database\Factories\SourceFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'api_identifier',
        'url',
        'description',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the articles for the source.
     *
     * @return HasMany<Article, $this>
     */
    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    /**
     * Scope a query to only include active sources.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Source>  $query
     * @return \Illuminate\Database\Eloquent\Builder<Source>
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
