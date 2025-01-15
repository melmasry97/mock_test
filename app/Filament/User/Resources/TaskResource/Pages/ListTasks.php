<?php

namespace App\Filament\User\Resources\TaskResource\Pages;

use App\Filament\User\Resources\TaskResource;
use Filament\Resources\Pages\ListRecords;

class ListTasks extends ListRecords
{
    protected static string $resource = TaskResource::class;
}
