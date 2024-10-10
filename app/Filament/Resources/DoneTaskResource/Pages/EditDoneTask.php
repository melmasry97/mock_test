<?php

namespace App\Filament\Resources\DoneTaskResource\Pages;

use App\Filament\Resources\DoneTaskResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDoneTask extends EditRecord
{
    protected static string $resource = DoneTaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    public function getTitle(): string
    {
        return 'Edit Backlog Task';
    }
}
