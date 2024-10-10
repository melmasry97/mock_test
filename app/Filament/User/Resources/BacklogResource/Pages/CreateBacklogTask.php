<?php

namespace App\Filament\User\Resources\BacklogResource\Pages;

use App\Filament\User\Resources\BacklogResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBacklogTask extends CreateRecord
{
    protected static string $resource = BacklogResource::class;
}
