<?php

namespace App\Http\Requests\Product;

use App\Http\Requests\BaseRequest;

class ProductRatingIndexRequest extends BaseRequest
{
    /**
     * Summery: Get the validation rules that apply to the request
     */
    public function rules(): array
    {
        return [
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * Summery: Ratings per page (default 10)
     */
    public function perPage(): int
    {
        return (int) ($this->validated('per_page') ?? 10);
    }
}
