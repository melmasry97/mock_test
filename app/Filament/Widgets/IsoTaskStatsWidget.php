<?php

namespace App\Filament\Widgets;

use App\Models\IsoTask;
use Filament\Widgets\Widget;
use Livewire\Attributes\On;

class IsoTaskStatsWidget extends Widget
{
    protected static string $view = 'filament.widgets.iso-task-stats-widget';

    public $totalWeight = 0;
    public $remainingWeight = 0;

    public function mount()
    {
        $this->calculateWeights();
    }

    #[On('iso-task-updated')]
    public function calculateWeights()
    {
        $this->totalWeight = IsoTask::sum('weight');
        $this->remainingWeight = max(0, 100 - $this->totalWeight);
    }

    protected function getViewData(): array
    {
        return [
            'totalWeight' => $this->totalWeight,
            'remainingWeight' => $this->remainingWeight,
        ];
    }
}
