<?php

namespace App\View\Components\JsonLd;

use App\Models\Facility;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use JsonLd\Context;
use JsonLd\ContextTypes\Place;

class FacilityPlace extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(public Facility $facility)
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $context = Context::create(Place::class, [
            'name' => $this->facility->name,
            'address' => $this->facility->area->address.$this->facility->address,
        ]);

        return view('components.json-ld.facility-place')->with(compact('context'));
    }
}
