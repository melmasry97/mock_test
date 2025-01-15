<?php

namespace App\Filament\User\Resources\IsoTaskResource\Pages;

use App\Filament\User\Resources\IsoTaskResource;
use Filament\Resources\Pages\ListRecords;
use App\Models\IsoTask;

class ListIsoTasks extends ListRecords
{
    protected static string $resource = IsoTaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Remove create action for users
        ];
    }

    public function getTitle(): string
    {
        return 'ISO25010 QAs';
    }
}
