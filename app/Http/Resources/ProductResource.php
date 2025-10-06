<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'images' => $this->images ?? [],
            'thumbnail' => $this->thumbnail,
            'price' => $this->price,
            'stock_quantity' => $this->stock_quantity,
            'category' => $this->when($this->relationLoaded('category') && $this->category, [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ]),
            'condition' => $this->condition,
            'allow_pickup' => (bool) $this->allow_pickup,
            'allow_shipping' => (bool) $this->allow_shipping,
            // minimal vendor info only (contact hidden on public listing)
            'vendor' => $this->when($this->relationLoaded('vendor') && $this->vendor, [
                'id' => $this->vendor->id,
                'business_name' => $this->vendor->business_name,
                'category' => $this->vendor->category ?? null,
            ]),
            'created_at' => $this->created_at,
        ];
    }
}
