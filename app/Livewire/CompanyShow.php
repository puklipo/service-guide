<?php

namespace App\Livewire;

use App\Models\Company;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class CompanyShow extends Component
{
    use WithPagination;

    public Company $company;

    #[Computed]
    public function facilities()
    {
        return $this->company->facilities()->simplePaginate(10)->withQueryString();
    }
    public function render()
    {
        return view('livewire.company-show')->title($this->company->name);
    }
}
