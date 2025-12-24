<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Update User Preference Request
 *
 * Validates user preference updates
 */
class UpdateUserPreferenceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'preferred_sources' => ['sometimes', 'array'],
            'preferred_sources.*' => ['integer', 'exists:sources,id'],
            'preferred_categories' => ['sometimes', 'array'],
            'preferred_categories.*' => ['integer', 'exists:categories,id'],
            'preferred_authors' => ['sometimes', 'array'],
            'preferred_authors.*' => ['integer', 'exists:authors,id'],
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
            'preferred_sources.array' => 'Preferred sources must be an array.',
            'preferred_sources.*.exists' => 'One or more selected sources do not exist.',
            'preferred_categories.array' => 'Preferred categories must be an array.',
            'preferred_categories.*.exists' => 'One or more selected categories do not exist.',
            'preferred_authors.array' => 'Preferred authors must be an array.',
            'preferred_authors.*.exists' => 'One or more selected authors do not exist.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Ensure arrays are set even if not provided
        if (! $this->has('preferred_sources')) {
            $this->merge(['preferred_sources' => []]);
        }
        if (! $this->has('preferred_categories')) {
            $this->merge(['preferred_categories' => []]);
        }
        if (! $this->has('preferred_authors')) {
            $this->merge(['preferred_authors' => []]);
        }
    }
}
