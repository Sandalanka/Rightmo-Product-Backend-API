<?php

namespace App\Http\Resources\Product;

use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin ProductImage
 */
class ProductImageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // Needed for the replace / delete image endpoints
            'id' => $this->id,
            'image_url' => Storage::disk('public')->url($this->image_path),
        ];
    }
}
