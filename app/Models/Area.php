<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperArea
 */
class Area extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
    ];

    public function pref(): BelongsTo
    {
        return $this->belongsTo(Pref::class);
    }

    public function facilities(): HasMany
    {
        return $this->hasMany(Facility::class);
    }
}
