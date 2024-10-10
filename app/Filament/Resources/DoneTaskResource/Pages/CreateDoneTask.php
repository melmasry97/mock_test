<?php

namespace App\Filament\Resources\DoneTaskResource\Pages;

use App\Filament\Resources\DoneTaskResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDoneTask extends CreateRecord
{
    protected static string $resource = DoneTaskResource::class;

    public function getTitle(): string
    {
        return 'Create Backlog Task';
    }
}
