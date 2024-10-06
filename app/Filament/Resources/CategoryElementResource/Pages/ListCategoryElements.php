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
}
