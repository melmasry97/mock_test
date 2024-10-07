<?php

namespace App\Filament\Resources\IsoTaskResource\Pages;

use App\Filament\Resources\IsoTaskResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListIsoTasks extends ListRecords
{
    protected static string $resource = IsoTaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
