<?php

namespace App\Filament\Resources\TypeResource\Pages;

use App\Filament\Resources\TypeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateType extends CreateRecord
{
    protected static string $resource = TypeResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // If project_id is passed in URL, use it
        if (request()->has('project_id')) {
            $data['project_id'] = request()->get('project_id');
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        // If we came from a project, redirect back to it
        if (request()->has('project_id')) {
            return route('filament.admin.resources.projects.edit', ['record' => request()->get('project_id')]);
        }

        return $this->getResource()::getUrl('index');
    }
}
