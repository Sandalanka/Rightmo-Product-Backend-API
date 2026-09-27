<?php

namespace App\Http\Requests\Product;

use App\Http\Requests\BaseRequest;

class ProductImageStoreRequest extends BaseRequest
{
    /**
     * Summery: Get the validation rules that apply to the request
     */
    public function rules(): array
    {
        return [
            'images' => ['required', 'array', 'min:1', 'max:10'],
            'images.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
