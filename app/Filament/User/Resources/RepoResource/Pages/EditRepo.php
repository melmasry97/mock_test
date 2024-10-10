<?php

namespace App\Filament\User\Resources\RepoResource\Pages;

use App\Filament\User\Resources\RepoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRepo extends EditRecord
{
    protected static string $resource = RepoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
