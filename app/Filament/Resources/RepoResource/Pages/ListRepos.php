<?php

namespace App\Filament\Resources\RepoResource\Pages;

use App\Filament\Resources\RepoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRepos extends ListRecords
{
    protected static string $resource = RepoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Create button removed
        ];
    }

    public function getTitle(): string
    {
        return 'Repo Tasks';
    }
}
