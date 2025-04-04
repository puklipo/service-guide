<?php

namespace App\Models;

use App\Support\IndexNow;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Random\RandomException;

use function Illuminate\Events\queueable;

/**
 * @mixin IdeHelperFacility
 */
class Facility extends Model
{
    use HasFactory;
    use HasUlids;

    protected $with = ['service', 'area', 'company'];

    protected $fillable = [
        'wam',
        'name',
        'name_kana',
        'address',
        'tel',
        'url',
        'no',
        'pref_id',
        'area_id',
        'company_id',
        'service_id',
    ];

    /**
     * @throws RandomException
     */
    protected static function booted(): void
    {
        if (app()->isProduction()) {
            static::created(queueable(function (Facility $facility) {
                IndexNow::submit(route('facility', $facility));
            })->delay(now()->addMinutes(random_int(1, 10))));

            static::updated(queueable(function (Facility $facility) {
                IndexNow::submit(route('facility', $facility));
            }));
        }
    }

    public function pref(): BelongsTo
    {
        return $this->belongsTo(Pref::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
