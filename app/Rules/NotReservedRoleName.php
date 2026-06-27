<?php

declare(strict_types = 1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class NotReservedRoleName implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_string($value) && mb_strtolower(trim($value)) === 'admin') {
            $fail('validation.not_in')->translate();
        }
    }
}
