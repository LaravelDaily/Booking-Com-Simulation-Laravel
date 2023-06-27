<?php

namespace App\Http\Resources\Api\V2;

use App\Models\Apartment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Apartment */
class ApartmentDetailsResource extends JsonResource
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
            'facility_categories' => $this->facility_categories,
            'wheelchair_access' => $this->wheelchair_access,
            'pets_allowed' => $this->pets_allowed,
            'smoking_allowed' => $this->smoking_allowed,
            'free_cancellation' => $this->free_cancellation,
            'all_day_access' => $this->all_day_access,
        ];
    }
}
