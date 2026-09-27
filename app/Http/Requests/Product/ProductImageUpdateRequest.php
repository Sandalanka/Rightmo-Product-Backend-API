<?php

namespace App\Http\Requests\Product;

use App\Http\Requests\BaseRequest;

class ProductImageUpdateRequest extends BaseRequest
{
    /**
     * Summery: Get the validation rules that apply to the request
     */
    public function rules(): array
    {
        return [
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
