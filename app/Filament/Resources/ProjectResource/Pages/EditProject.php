<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use App\Models\Type;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditProject extends EditRecord
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\DeleteAction::make(),
        ];
    }

    public function evaluateCategories($typeId)
    {
        return redirect()->to(EvaluateCategories::getUrl([
            'record' => $this->record,
            'typeId' => $typeId
        ]));
    }

    public function detachType($typeId)
    {
        $this->record->types()->detach($typeId);
        Notification::make()
            ->success()
            ->title('Type detached successfully')
            ->send();
    }
}
