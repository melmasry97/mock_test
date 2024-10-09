<?php

namespace App\Filament\Resources\RepoResource\Pages;

use App\Filament\Resources\RepoResource;
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

    public function getTitle(): string
    {
        return 'Edit Repo Task';
    }
}
