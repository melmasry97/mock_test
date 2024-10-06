<?php

namespace App\Filament\Resources\CategoryElementResource\Pages;

use App\Filament\Resources\CategoryElementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCategoryElement extends EditRecord
{
    protected static string $resource = CategoryElementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
