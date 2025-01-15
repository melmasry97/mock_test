<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use App\Models\Type;
use Filament\Forms;
use Filament\Resources\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Route;

class EvaluateCategories extends Page
{
    protected static string $resource = ProjectResource::class;

    protected static string $view = 'filament.resources.project-resource.pages.evaluate-categories';

    public $project;
    public $type;
    public $categories;

    public function mount($record, $typeId)
    {
        $this->project = $record;
        $this->type = Type::findOrFail($typeId);
        $this->categories = $this->project->typeCategories()
            ->wherePivot('type_id', $typeId)
            ->get();
    }

    public function getTitle(): string
    {
        return "Evaluate Categories for {$this->type->name}";
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Section::make('Category Weights')
                ->description('The sum of all weights must equal 1')
                ->schema(
                    $this->categories->map(function ($category) {
                        return Forms\Components\TextInput::make("weights.{$category->id}")
                            ->label($category->name)
                            ->default($category->pivot->weight ?? 0)
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(1)
                            ->step(0.1)
                            ->required();
                    })->toArray()
                )
        ];
    }

    public function submit()
    {
        $data = $this->form->getState();
        $weights = collect($data['weights']);

        $total = $weights->sum();

        if (abs($total - 1) > 0.001) {
            Notification::make()
                ->danger()
                ->title('Invalid weights')
                ->body("The sum of weights must equal 1. Current sum: {$total}")
                ->send();
            return;
        }

        foreach ($weights as $categoryId => $weight) {
            $this->project->typeCategories()
                ->wherePivot('category_id', $categoryId)
                ->wherePivot('type_id', $this->type->id)
                ->updateExistingPivot($categoryId, [
                    'weight' => floatval($weight),
                    'updated_at' => now()
                ]);
        }

        Notification::make()
            ->success()
            ->title('Category weights updated successfully')
            ->send();

        return redirect()->route('filament.admin.resources.projects.edit', $this->project);
    }

    public static function getUrl(array $parameters = [], bool $isAbsolute = true, ?string $panel = null, ?\Illuminate\Database\Eloquent\Model $tenant = null): string
    {
        return route('filament.admin.resources.projects.evaluate-categories', [
            'record' => $parameters['record'] ?? null,
            'typeId' => $parameters['typeId'] ?? null,
        ], $isAbsolute);
    }
}
