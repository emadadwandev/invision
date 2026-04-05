<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'category_id' => $this->category_id,
            'category' => new ProductCategoryResource($this->whenLoaded('category')),
            'name' => $this->name,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'description' => $this->description,
            'image_path' => $this->image_path,
            'is_active' => $this->is_active,
            'price_levels' => ProductPriceLevelResource::collection($this->whenLoaded('priceLevels')),
            'current_price' => ProductPriceLevelResource::collection($this->whenLoaded('currentPrice')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
