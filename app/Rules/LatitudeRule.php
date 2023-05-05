<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class LatitudeRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $regex = '/^[-]?((([0-8]?[0-9])(\.(\d{1,8}))?)|(90(\.0+)?))$/';

        if (! preg_match($regex, $value)) {
            $fail('The :attribute must be a valid latitude coordinate in decimal degrees format.');
        }
    }
}
