<?php

namespace App\Filament\User\Resources\ProjectResource\Pages;

use App\Filament\User\Resources\ProjectResource;
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
        if ($this->project->evaluation_end_time && now()->isAfter($this->project->evaluation_end_time)) {
            Notification::make()
                ->warning()
                ->title('Evaluation period has ended')
                ->send();

            return redirect()->route('filament.user.resources.projects.view', $this->project);
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

            return redirect()->route('filament.user.resources.projects.view', $this->project);
        }

        // Initialize weights with current values or from existing evaluations
        foreach ($this->categories as $category) {
            $evaluation = ProjectTypeCategoryEvaluation::where([
                'project_id' => $this->project->id,
                'type_id' => $this->type->id,
                'category_id' => $category->id,
                'user_id' => Auth::id(),
            ])->first();

            $this->weights[$category->id] = $evaluation ? $evaluation->weight : 0;
        }
    }

    public function getTitle(): string
    {
        return "Evaluate Categories for {$this->type->name}";
    }

    public function getFormSchema(): array
    {
        $schema = [];

        foreach ($this->categories as $category) {
            $schema[] = TextInput::make("weights.{$category->id}")
                ->label($category->name)
                ->helperText($category->description)
                ->numeric()
                ->step(0.01)
                ->minValue(0)
                ->maxValue(1)
                ->required()
                ->default($this->weights[$category->id] ?? 0);
        }

        return $schema;
    }

    public function submit()
    {
        $weights = $this->weights;
        $total = array_sum($weights);

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
                    'evaluation_end_time' => $this->project->evaluation_end_time
                ]
            );
        }

        Notification::make()
            ->success()
            ->title('Categories evaluated successfully')
            ->send();

        return redirect()->route('filament.user.resources.projects.view', $this->project);
    }

    public static function getUrl(array $parameters = [], bool $isAbsolute = true, ?string $panel = null, ?\Illuminate\Database\Eloquent\Model $tenant = null): string
    {
        return route('filament.user.resources.projects.evaluate-categories', [
            'record' => $parameters['record'] ?? null,
            'typeId' => $parameters['typeId'] ?? null,
        ], $isAbsolute);
    }
}
