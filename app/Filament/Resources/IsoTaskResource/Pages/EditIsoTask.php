<?php

namespace App\Filament\Resources\IsoTaskResource\Pages;

use App\Filament\Resources\IsoTaskResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIsoTask extends EditRecord
{
    protected static string $resource = IsoTaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
