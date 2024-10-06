<?php

namespace App\Filament\Resources\CategoryElementResource\Pages;

use App\Filament\Resources\CategoryElementResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCategoryElements extends ListRecords
{
    protected static string $resource = CategoryElementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getTableEmptyStateHeading(): ?string
    {
        return 'No category elements yet';
    }

    protected function getTableEmptyStateDescription(): ?string
    {
        return 'Create your first category element by clicking the button below.';
    }

    protected function getTableEmptyStateActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
