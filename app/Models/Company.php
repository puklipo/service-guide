<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperCompany
 */
class Company extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'name_kana',
        'area',
        'address',
        'tel',
        'url',
    ];

    public function facilities(): HasMany
    {
        return $this->hasMany(Facility::class);
    }
}
