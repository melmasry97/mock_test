<?php

namespace App\Filament\Resources\TypeResource\Pages;

use App\Filament\Resources\TypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditType extends EditRecord
{
    protected static string $resource = TypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function ($record) {
                    if ($record->categories()->count() > 0) {
                        throw new \Exception('Cannot delete type that has categories.');
                    }
                    if ($record->tasks()->count() > 0) {
                        throw new \Exception('Cannot delete type that has associated tasks.');
                    }
                }),
        ];
    }
}
