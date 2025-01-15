<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\EditRecord;

class EditProject extends EditRecord
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    public function evaluateCategories($typeId)
    {
        $type = $this->record->types()->findOrFail($typeId);

        $this->mountAction('evaluate_categories', [
            'type' => $type,
            'categories' => $type->categories->map(function ($category) {
                $pivot = $category->projects()
                    ->where('projects.id', $this->record->id)
                    ->first()
                    ?->pivot;

                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'weight' => $pivot?->weight ?? 0,
                ];
            }),
        ]);
    }

    public function detachType($typeId)
    {
        $this->record->types()->detach($typeId);
        $this->notify('success', 'Type detached successfully');
    }

    protected function getActions(): array
    {
        return [
            Actions\Action::make('evaluate_categories')
                ->form(function (array $data): array {
                    return $data['categories']->map(function ($category) {
                        return Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\TextInput::make("categories.{$category['id']}.name")
                                    ->label($category['name'])
                                    ->disabled(),
                                Forms\Components\TextInput::make("categories.{$category['id']}.weight")
                                    ->label('Weight')
                                    ->numeric()
                                    ->default($category['weight'])
                                    ->required(),
                            ])
                            ->columns(2);
                    })->toArray();
                })
                ->action(function (array $data) {
                    foreach ($data['categories'] as $categoryId => $values) {
                        $this->record->typeCategories()
                            ->wherePivot('category_id', $categoryId)
                            ->wherePivot('type_id', $data['type']->id)
                            ->update([
                                'weight' => $values['weight']
                            ]);
                    }

                    $this->notify('success', 'Categories evaluated successfully');
                }),
        ];
    }
}
