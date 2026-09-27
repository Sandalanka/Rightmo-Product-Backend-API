<?php

namespace App\Http\Resources\Product;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * Product in the product list (use ProductResource for the full product details)
 *
 * @mixin Product
 */
class ProductListResource extends JsonResource
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
            'price' => $this->price,
            // Average of all ratings rounded to one decimal (e.g. 4.7), 0 when the product has no ratings
            'average_rating' => round((float) $this->ratings_avg_rating, 1),
            'category_name' => $this->whenLoaded('category', fn () => $this->category->name),
            // URL of the first image only (null when the product has no images)
            'image_url' => $this->whenLoaded('firstImage', fn () => $this->firstImage
                ? Storage::disk('public')->url($this->firstImage->image_path)
                : null),
        ];
    }
}
