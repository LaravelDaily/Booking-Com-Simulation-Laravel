<?php

namespace App\Http\Requests\Api\V2;

use App\Rules\ApartmentAvailableRule;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StoreBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('bookings-manage');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'apartment_id' => ['required', 'exists:apartments,id', new ApartmentAvailableRule()],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
            'guests_adults' => ['integer'],
            'guests_children' => ['integer'],
            'guests' => ['required', 'array', 'size:' . $this->input('guests_adults') + $this->input('guests_children')],
            'guests.*.first_name' => ['required', 'string'],
            'guests.*.last_name' => ['required', 'string'],
            'guests.*.birth_date' => ['required', 'date'],
        ];
    }
}
