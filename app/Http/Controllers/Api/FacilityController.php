<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FacilityResource;
use App\Models\Area;
use App\Models\Facility;
use App\Models\Pref;
use App\Models\Service;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class FacilityController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $serviceFilter = $request->input('service');
        $prefFilter = $request->input('pref');
        $areaFilter = $request->input('area');

        $service = $serviceFilter ? Service::where('name', 'LIKE', '%'.$serviceFilter.'%')->first() : null;
        $pref = $prefFilter ? Pref::where('name', 'LIKE', '%'.$prefFilter.'%')->first() : null;
        $area = $areaFilter ? Area::where('name', 'LIKE', '%'.$areaFilter.'%')->first() : null;

        $facilities = Facility::when($serviceFilter, function (Builder $query) use ($service) {
            if ($service) {
                $query->where('service_id', $service->id);
            } else {
                // If service filter is provided but no service found, return empty results
                $query->where('id', -1);
            }
        })->when($prefFilter, function (Builder $query) use ($pref) {
            if ($pref) {
                $query->where('pref_id', $pref->id);
            } else {
                // If pref filter is provided but no pref found, return empty results
                $query->where('id', -1);
            }
        })->when($areaFilter, function (Builder $query) use ($area) {
            if ($area) {
                $query->where('area_id', $area->id);
            } else {
                // If area filter is provided but no area found, return empty results
                $query->where('id', -1);
            }
        })
            ->latest()
            ->simplePaginate()
            ->withQueryString();

        return FacilityResource::collection($facilities);
    }
}
