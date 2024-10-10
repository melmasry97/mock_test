<?php

namespace App\Filament\User\Resources\BacklogResource\Pages;

use App\Filament\User\Resources\BacklogResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBacklogTask extends EditRecord
{
    protected static string $resource = BacklogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
