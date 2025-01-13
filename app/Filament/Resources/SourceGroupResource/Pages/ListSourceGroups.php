<?php

namespace App\Filament\Resources\SourceGroupResource\Pages;

use App\Filament\Resources\SourceGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSourceGroups extends ListRecords
{
    protected static string $resource = SourceGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
