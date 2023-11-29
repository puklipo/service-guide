<?php

namespace App\Livewire;

use App\Models\Facility;
use App\Models\Service;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class FacilityShow extends Component
{
    use WithPagination;

    public Service $service;
    public Facility $facility;

    #[Computed]
    public function area_facilities()
    {
        return Facility::where('area_id', $this->facility->area_id)
            ->where('service_id', $this->facility->service_id)
            ->latest('updated_at')
            ->simplePaginate(10)
            ->withQueryString();
    }

    public function render()
    {
        return view('livewire.facility-show')
            ->title($this->facility->name);
    }
}
