<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use App\Models\Type;
use App\Models\Project;
use App\Models\ProjectTypeCategoryEvaluation;
use Filament\Forms;
use Filament\Resources\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Route;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Auth;

class EvaluateCategories extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static string $resource = ProjectResource::class;

    protected static string $view = 'filament.resources.project-resource.pages.evaluate-categories';

    public $project;
    public $type;
    public $categories;
    public $weights = [];

    public function mount($record, $typeId)
    {
        $this->project = Project::findOrFail($record);
        $this->type = Type::findOrFail($typeId);

        // Check if evaluation time has ended
        if ($this->type->evaluation_end_time && now()->isAfter($this->type->evaluation_end_time)) {
            Notification::make()
                ->warning()
                ->title('Evaluation period has ended')
                ->send();

            return redirect()->route('filament.admin.resources.projects.edit', $this->project);
        }

        $this->categories = $this->project->typeCategories()
            ->wherePivot('type_id', $typeId)
            ->get();

        // Check if there are categories
        if ($this->categories->isEmpty()) {
            Notification::make()
                ->warning()
                ->title('No categories to evaluate')
                ->send();

            return redirect()->route('filament.admin.resources.projects.edit', $this->project);
        }

        // Initialize weights with current values
        foreach ($this->categories as $category) {
            $this->weights[$category->id] = $category->pivot->weight ?? 0;
        }
    }

    public function getTitle(): string
    {
        return "Evaluate Categories for {$this->type->name}";
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Section::make('Category Weights')
                ->description('The sum of all weights must be less than or equal to 1')
                ->schema(
                    $this->categories->map(function ($category) {
                        return TextInput::make("weights.{$category->id}")
                            ->label($category->name)
                            ->default($this->weights[$category->id])
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
        $formData = $this->form->getState();

        // Collect weights
        $weights = collect($formData['weights'] ?? [])->map(function ($weight) {
            return floatval($weight);
        });

        $total = $weights->sum();

        if ($total > 1) {
            Notification::make()
                ->danger()
                ->title('Invalid weights')
                ->body("The sum of weights must be less than or equal to 1. Current sum: {$total}")
                ->send();
            return;
        }

        // Save evaluations
        foreach ($weights as $categoryId => $weight) {
            ProjectTypeCategoryEvaluation::updateOrCreate(
                [
                    'project_id' => $this->project->id,
                    'type_id' => $this->type->id,
                    'category_id' => $categoryId,
                    'user_id' => Auth::id(),
                ],
                [
                    'weight' => $weight,
                    'evaluation_end_time' => $this->type->evaluation_end_time
                ]
            );

            // Update the average weight in the pivot table
            $avgWeight = ProjectTypeCategoryEvaluation::where([
                'project_id' => $this->project->id,
                'type_id' => $this->type->id,
                'category_id' => $categoryId,
            ])->avg('weight');

            $this->project->typeCategories()
                ->wherePivot('category_id', $categoryId)
                ->wherePivot('type_id', $this->type->id)
                ->updateExistingPivot($categoryId, [
                    'weight' => $avgWeight,
                    'updated_at' => now()
                ]);
        }

        Notification::make()
            ->success()
            ->title('Categories evaluated successfully')
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
