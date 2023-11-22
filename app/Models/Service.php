<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperService
 */
class Service extends Model
{
    use HasFactory;

    public $incrementing = false;

    public function facilities(): HasMany
    {
        return $this->hasMany(Facility::class);
    }
}
