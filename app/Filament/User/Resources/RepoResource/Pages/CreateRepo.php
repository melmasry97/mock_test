<?php

namespace App\Filament\User\Resources\RepoResource\Pages;

use App\Filament\User\Resources\RepoResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateRepo extends CreateRecord
{
    protected static string $resource = RepoResource::class;
}
