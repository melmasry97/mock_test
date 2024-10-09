<?php

namespace App\Filament\Resources\IsoTaskResource\Pages;

use App\Filament\Resources\IsoTaskResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\IsoTask;
use Filament\Notifications\Notification;

class CreateIsoTask extends CreateRecord
{
    protected static string $resource = IsoTaskResource::class;

    protected function beforeCreate(): void
    {
        $taskCount = IsoTask::count();
        if ($taskCount >= 9) {
            Notification::make()
                ->title('Maximum number of ISO tasks reached')
                ->body('You cannot create more than 9 ISO tasks.')
                ->danger()
                ->send();

            $this->halt();
        }

        $newWeight = $this->data['weight'];
        $totalWeight = IsoTask::sum('weight') + $newWeight;

        if ($totalWeight > 100) {
            Notification::make()
                ->title('Total weight exceeds 100%')
                ->body("The total weight of all ISO tasks cannot exceed 100%. Current total: {$totalWeight}%")
                ->danger()
                ->send();

            $this->halt();
        }
    }

    protected function afterCreate(): void
    {
        // Dispatch the event after creating the ISO task
        $this->dispatch('iso-task-updated');
    }
}
