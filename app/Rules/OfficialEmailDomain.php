<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class OfficialEmailDomain implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $domain = strtolower((string) substr(strrchr((string) $value, '@'), 1));

        $allowedDomains = [
            'gmail.com',
            'yahoo.com',
            'yahoo.co.id',
            'outlook.com',
            'hotmail.com',
            'icloud.com',
            'belajar.id',
        ];

        $isAllowed = in_array($domain, $allowedDomains)
            || str_ends_with($domain, '.sch.id')
            || str_ends_with($domain, '.ac.id');

        if (! $isAllowed) {
            $fail('Gunakan alamat email resmi (@gmail.com, @yahoo.com, @outlook.com, @icloud.com, atau @belajar.id). Penggunaan email sementara (temp mail) tidak diizinkan.');
        }
    }
}

