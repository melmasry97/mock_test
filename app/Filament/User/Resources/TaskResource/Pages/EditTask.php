<?php

namespace App\Filament\User\Resources\TaskResource\Pages;

use App\Filament\User\Resources\TaskResource;
use Filament\Resources\Pages\EditRecord;

class EditTask extends EditRecord
{
    protected static string $resource = TaskResource::class;
}
