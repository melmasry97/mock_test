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
        if (IsoTask::count() >= 9) {
            Notification::make()
                ->title('ISO Task limit reached')
                ->body('You can only create up to 9 ISO Tasks.')
                ->danger()
                ->send();

            $this->halt();
        }
    }
}
