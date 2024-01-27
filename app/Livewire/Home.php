<?php

namespace App\Livewire;

use App\Models\Area;
use App\Models\Facility;
use App\Models\Pref;
use App\Models\Service;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Home extends Component
{
    use WithPagination;

    #[Url]
    #[Validate('numeric|integer')]
    public string $pref = '';

    #[Url]
    #[Validate('numeric|integer')]
    public string $area = '';

    #[Url]
    #[Validate('numeric|integer')]
    public string $service = '';

    #[Url]
    #[Validate('numeric|integer')]
    public int $limit = 100;

    #[Computed]
    public function facilities()
    {
        return Facility::when(filled($this->service), function (Builder $query) {
            $query->where('service_id', $this->service);
        })->when(filled($this->pref), function (Builder $query) {
            $query->where('pref_id', $this->pref);
        })->when(filled($this->area), function (Builder $query) {
            $query->where('area_id', $this->area);
        })
            ->latest()
            ->simplePaginate($this->limit)
            ->withQueryString();
    }

    #[Computed]
    public function prefs()
    {
        return Pref::withCount(['facilities'])->orderBy('id')->get();
    }

    public function updatedPref()
    {
        $this->area = '';
    }

    #[Computed]
    public function areas()
    {
        if (blank($this->pref)) {
            return [];
        }

        return Area::withCount(['facilities'])
            ->where('pref_id', $this->pref)
            ->orderByDesc('facilities_count')
            ->get();
    }

    #[Computed]
    public function services()
    {
        return Service::has('facilities')->withCount(['facilities' => function (Builder $query) {
            $query->when(filled($this->area), function (Builder $query) {
                $query->where('area_id', $this->area);
            })->when(filled($this->pref), function (Builder $query) {
                $query->where('pref_id', $this->pref);
            });
        }])->orderByDesc('facilities_count')->get();
    }

    public function render()
    {
        $title = collect()
            ->push(Pref::find($this->pref)?->name)
            ->push(Area::find($this->area)?->name)
            ->push(Service::find($this->service)?->name)
            ->push(config('app.name'))
            ->implode(' ');

        return view('livewire.home')->title(trim($title));
    }
}
