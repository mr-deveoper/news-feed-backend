<?php

namespace App\Http\Requests\Article;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Search Article Request
 *
 * Validates search and filter parameters for article listing
 */
class SearchArticleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Public endpoint
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'keyword' => ['sometimes', 'string', 'max:255'],
            'from' => ['sometimes', 'date', 'date_format:Y-m-d'],
            'to' => ['sometimes', 'date', 'date_format:Y-m-d', 'after_or_equal:from'],
            'source_ids' => ['sometimes', 'array'],
            'source_ids.*' => [
                'required',
                'integer',
                Rule::exists('sources', 'id')->where('is_active', true),
            ],
            'category_ids' => ['sometimes', 'array'],
            'category_ids.*' => ['required', 'integer', 'exists:categories,id'],
            'author_ids' => ['sometimes', 'array'],
            'author_ids.*' => ['required', 'integer', 'exists:authors,id'],
            'sort_by' => ['sometimes', 'string', 'in:published_at,created_at,title'],
            'sort_order' => ['sometimes', 'string', 'in:asc,desc'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'from.date' => 'The from date must be a valid date in Y-m-d format (e.g., 2024-01-01).',
            'to.date' => 'The to date must be a valid date in Y-m-d format (e.g., 2024-12-31).',
            'to.after_or_equal' => 'The to date must be equal to or after the from date.',
            'source_ids.*.exists' => 'One or more selected sources do not exist.',
            'category_ids.*.exists' => 'One or more selected categories do not exist.',
            'author_ids.*.exists' => 'One or more selected authors do not exist.',
            'sort_by.in' => 'The sort_by field must be one of: published_at, created_at, title.',
            'sort_order.in' => 'The sort_order field must be either asc or desc.',
            'per_page.max' => 'The per_page field cannot exceed 100.',
        ];
    }
}
