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
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Home extends Component
{
    use WithPagination;

    #[Url]
    public ?string $service = '';
    #[Url]
    public ?string $pref = '';
    #[Url]
    public ?string $area = '';
    #[Url]
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
            ->latest('updated_at')
            ->simplePaginate($this->limit)
            ->withQueryString();
    }

    #[Computed]
    public function prefs()
    {
        return Pref::withCount(['facilities'])->orderBy('id')->get();
    }

    #[Computed]
    public function areas()
    {
        return Area::withCount(['facilities'])->when(filled($this->pref), function (Builder $query) {
            $query->where('pref_id', $this->pref);
        })->orderByDesc('facilities_count')->get();
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
        }])->orderBy('id')->get();
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
