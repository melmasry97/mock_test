<?php

namespace App\Filament\User\Resources\BacklogResource\Pages;

use App\Filament\User\Resources\BacklogResource;
use Filament\Resources\Pages\ListRecords;
use App\Jobs\CalculateTaskWeight;

class ListBacklogTasks extends ListRecords
{
    protected static string $resource = BacklogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Add any header actions if needed
        ];
    }

    protected function beforeFill(): void
    {
        // Run the CalculateTaskWeight job synchronously
        (new CalculateTaskWeight())->handle();
    }
}
