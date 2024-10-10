<?php

namespace App\Filament\User\Resources\BacklogResource\Pages;

use App\Filament\User\Resources\BacklogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBacklogTasks extends ListRecords
{
    protected static string $resource = BacklogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
