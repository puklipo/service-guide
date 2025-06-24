<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class Telephone implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        $patch = config('patch', []);
        if (!is_array($patch)) {
            return $value;
        }
        
        $patchedValue = Arr::get($patch, $model->id.'.tel', $value);
        return is_string($patchedValue) ? $patchedValue : $value;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        // Handle null values explicitly
        if (is_null($value)) {
            return null;
        }
        
        // Handle string values (most common case)
        if (is_string($value)) {
            return trim($value) === '' ? null : $value;
        }
        
        // Handle numeric values (convert to string)
        if (is_numeric($value)) {
            return (string) $value;
        }
        
        // Handle arrays/objects by converting to string or returning null
        if (is_array($value) || is_object($value)) {
            return null;
        }
        
        // For any other type, try to cast to string safely
        try {
            return (string) $value;
        } catch (\Throwable) {
            return null;
        }
    }
}
