<?php

namespace App\Filament\Resources\IsoTaskResource\Pages;

use App\Filament\Resources\IsoTaskResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Models\IsoTask;
use App\Filament\Widgets\IsoTaskStatsWidget; // Add this line

class ListIsoTasks extends ListRecords
{
    protected static string $resource = IsoTaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            IsoTaskStatsWidget::class,
        ];
    }

    public function getTitle(): string
    {
        return 'ISO25010 QAs';
    }
}
