<?php

namespace App\Http\Resources\Api\V2;

use App\Models\Apartment;
use App\Services\PricingService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Apartment */
class ApartmentSearchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'type' => $this->apartment_type?->name,
            'size' => $this->size,
            'beds_list' => $this->beds_list,
            'bathrooms' => $this->bathrooms,
            'facilities' => FacilityResource::collection($this->whenLoaded('facilities')),
            'price' => (new PricingService())->calculateApartmentPriceForDates($this->prices, $request->start_date, $request->end_date),
            'wheelchair_access' => $this->wheelchair_access,
            'pets_allowed' => $this->pets_allowed,
            'smoking_allowed' => $this->smoking_allowed,
            'free_cancellation' => $this->free_cancellation,
            'all_day_access' => $this->all_day_access,
        ];
    }
}
