<?php

namespace App\Filament\User\Resources\RepoResource\Pages;

use App\Filament\User\Resources\RepoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRepos extends ListRecords
{
    protected static string $resource = RepoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
