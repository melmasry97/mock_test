<?php

namespace App\Filament\Resources\RepoResource\Pages;

use App\Filament\Resources\RepoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRepo extends CreateRecord
{
    protected static string $resource = RepoResource::class;

    public function getTitle(): string
    {
        return 'Create Repo Task';
    }
}
