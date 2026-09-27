<?php

namespace App\Http\Requests\Product;

use App\Http\Requests\BaseRequest;
use App\Models\Category;
use Illuminate\Validation\Rule;

class ProductIndexRequest extends BaseRequest
{
    /**
     * Summery: Get the validation rules that apply to the request
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer', Rule::exists(Category::class, 'id')],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0', 'gte:min_price'],
            'sort_by' => ['nullable', Rule::in(['price', 'rating', 'name', 'created_at'])],
            'sort_order' => ['nullable', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * Summery: Validated filters with defaults applied
     *
     * @return array{search: ?string, category_id: ?int, min_price: ?float, max_price: ?float, sort_by: string, sort_order: string, per_page: int, page: int}
     */
    public function filters(): array
    {
        $validated = $this->validated();

        return [
            'search' => isset($validated['search']) ? trim($validated['search']) : null,
            'category_id' => isset($validated['category_id']) ? (int) $validated['category_id'] : null,
            'min_price' => isset($validated['min_price']) ? (float) $validated['min_price'] : null,
            'max_price' => isset($validated['max_price']) ? (float) $validated['max_price'] : null,
            'sort_by' => $validated['sort_by'] ?? 'created_at',
            'sort_order' => $validated['sort_order'] ?? 'desc',
            'per_page' => (int) ($validated['per_page'] ?? 15),
            'page' => (int) ($validated['page'] ?? 1),
        ];
    }
}
