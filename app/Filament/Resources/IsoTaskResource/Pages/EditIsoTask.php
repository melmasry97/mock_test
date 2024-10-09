<?php

namespace App\Filament\Resources\IsoTaskResource\Pages;

use App\Filament\Resources\IsoTaskResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\IsoTask;
use Filament\Notifications\Notification;

class EditIsoTask extends EditRecord
{
    protected static string $resource = IsoTaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function beforeSave(): void
    {
        $newWeight = $this->data['weight'];
        $currentWeight = $this->record->weight;
        $totalWeight = IsoTask::sum('weight') - $currentWeight + $newWeight;

        if ($totalWeight > 100) {
            Notification::make()
                ->title('Total weight exceeds 100%')
                ->body("The total weight of all ISO tasks cannot exceed 100%. Current total: {$totalWeight}%")
                ->danger()
                ->send();

            $this->halt();
        }
    }

    protected function afterSave(): void
    {
        $this->dispatch('iso-task-updated');
    }
}
