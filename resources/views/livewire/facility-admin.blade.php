<?php

use App\Models\Facility;

use function Livewire\Volt\mount;
use function Livewire\Volt\state;

state(['facility']);

mount(function (Facility $facility) {
    $this->facility = $facility;
});

$accept = function () {
    $this->authorize('admin');
    $this->facility->forceFill(['description' => $this->facility->description_draft])->save();
    $this->redirect(route('facility', $this->facility), navigate: true);
}
?>

<div>
    @if(filled($facility->description_draft))
        <x-danger-button class="mt-2" wire:click="accept">下書きを承認して公開</x-danger-button>
    @endif
</div>
