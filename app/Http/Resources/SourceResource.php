<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Source Resource
 *
 * Transforms Source model for API responses
 */
class SourceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'api_identifier' => $this->api_identifier,
            'url' => $this->url,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),

            // Count of articles if loaded
            'articles_count' => $this->when(
                $this->relationLoaded('articles'),
                fn () => $this->articles->count()
            ),
        ];
    }
}
