<?php

namespace App\Http\Requests\Product;

use App\Http\Requests\BaseRequest;

class ProductRatingStoreRequest extends BaseRequest
{
    /**
     * Summery: Get the validation rules that apply to the request
     */
    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
