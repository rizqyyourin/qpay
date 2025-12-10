<?php

namespace App\Livewire;

use Livewire\Component;

class LandingHighlights extends Component
{
    public array $metrics = [];

    public function mount()
    {
        $this->metrics = [
            ['label' => 'Live counters', 'value' => '64', 'meta' => 'Connected across 7 cities'],
            ['label' => 'Avg. checkout', 'value' => '45s', 'meta' => 'From scan to receipt'],
            ['label' => 'Return rate', 'value' => '2.1%', 'meta' => 'Lowered refunds with clarity'],
        ];
    }

    public function render()
    {
        return view('livewire.landing-highlights');
    }
}