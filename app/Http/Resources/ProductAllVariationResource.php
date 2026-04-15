<?php

namespace App\Http\Resources;


use App\Enums\Ask;
use Carbon\Carbon;
use App\Libraries\AppLibrary;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductAllVariationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'product_attribute_id' => $this->product_attribute_id,
            'product_attribute_option_id' => $this->product_attribute_option_id,
            'product_attribute_name' => $this->productAttribute?->name,
            'product_attribute_option_name' => $this->productAttributeOption?->name,
            'price' => Carbon::now()->between($this->product?->offer_start_date, $this->product?->offer_end_date) ? AppLibrary::convertAmountFormat(max(0, $this->price - $this->product?->discount)) : AppLibrary::convertAmountFormat($this->price),
            'currency_price' => Carbon::now()->between($this->product?->offer_start_date, $this->product?->offer_end_date) ? AppLibrary::currencyAmountFormat(max(0, $this->price - $this->product?->discount)) : AppLibrary::currencyAmountFormat($this->price),
            'old_price' => AppLibrary::convertAmountFormat($this->price),
            'old_currency_price' => AppLibrary::currencyAmountFormat($this->price),
            'discount' => Carbon::now()->between($this->product?->offer_start_date, $this->product?->offer_end_date) ? AppLibrary::convertAmountFormat($this->product?->discount) : 0,
            'discount_percentage' => $this->price > 0 ? AppLibrary::convertAmountFormat(($this->product?->discount / $this->price) * 100) : 0,
            'sku' => $this->sku,
            'stock' => $this->product?->can_purchasable == Ask::NO ? 0 : (int) ($this->stock_items_sum_quantity ?? 0),
            'children' => ProductAllVariationResource::collection($this->children),
            'media' => $this->getMedia('variant')->map(function ($m) {
                return ['url' => $m->getUrl(), 'original_url' => $m->getUrl()];
            })->toArray(),
        ];
    }
}
