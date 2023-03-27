<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
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
            'apartment_name' => $this->apartment->property->name . ': ' . $this->apartment->name,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'guests_adults' => $this->guests_adults,
            'guests_children' => $this->guests_children,
            'total_price' => $this->total_price,
            'cancelled_at' => $this->deleted_at?->toDateString(),
        ];
    }
}
