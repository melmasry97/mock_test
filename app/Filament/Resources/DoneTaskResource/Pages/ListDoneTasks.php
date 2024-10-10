<?php

namespace App\Filament\Resources\DoneTaskResource\Pages;

use App\Filament\Resources\DoneTaskResource;
use Filament\Resources\Pages\ListRecords;

class ListDoneTasks extends ListRecords
{
    protected static string $resource = DoneTaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Creation action removed
        ];
    }

    public function getTitle(): string
    {
        return 'Backlog Tasks';
    }
}
