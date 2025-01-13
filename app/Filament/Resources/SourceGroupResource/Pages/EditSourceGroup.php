<?php

namespace App\Filament\Resources\SourceGroupResource\Pages;

use App\Filament\Resources\SourceGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSourceGroup extends EditRecord
{
    protected static string $resource = SourceGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function ($record) {
                    if ($record->sources()->count() > 0) {
                        throw new \Exception('Cannot delete group that contains sources.');
                    }
                }),
        ];
    }
}
