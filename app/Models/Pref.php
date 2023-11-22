<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * @mixin IdeHelperPref
 */
class Pref extends Model
{
    use HasFactory;

    public function facilities(): HasMany
    {
        return $this->hasMany(Facility::class);
    }

    public function areas(): HasMany
    {
        return $this->hasMany(Area::class);
    }
}
