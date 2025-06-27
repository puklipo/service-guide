<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Spammer implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        Http::get('https://grouphome.guide/api/spam')
            ->collect()
            ->merge(config('spam'))
            ->unique()
            ->each(function ($mail) use ($value, $fail) {
                if (Str::is($mail, $value)) {
                    // logger()->warning('Spam: '.$value);

                    $fail('');

                    return false;
                }
            });
    }
}
