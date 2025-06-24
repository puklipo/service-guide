<?php

namespace App\View\Components\JsonLd;

use App\Models\Facility;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use JsonLd\Context;
use JsonLd\ContextTypes\LocalBusiness;

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
        $context = Context::create(LocalBusiness::class, [
            'name' => $this->facility->name,
            'address' => $this->facility->area->address.$this->facility->address,
            // 'telephone' => $this->facility->tel,
            'url' => filled($this->facility->url) ? $this->facility->url : route('facility', $this->facility),
        ]);

        return view('components.json-ld.facility-place')->with(compact('context'));
    }
}
