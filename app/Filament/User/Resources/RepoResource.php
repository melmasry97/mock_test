<?php

namespace App\Filament\User\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use App\Models\Task;
use App\Models\IsoTask;
use App\Models\Metric;
use App\Models\ProjectModule;
use App\Enums\TaskState;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\User\Resources\RepoResource\Pages;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RepoResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationLabel = 'Repos';

    protected static ?string $navigationGroup = 'Repo Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->maxLength(65535),
                Forms\Components\Hidden::make('state')
                    ->default(TaskState::REPO),
                Forms\Components\Select::make('project_module_id')
                    ->label('Project Module')
                    ->options(ProjectModule::all()->pluck('name', 'id'))
                    ->searchable()
                    ->required(),
                Forms\Components\DatePicker::make('end_date')
                    ->label('End Date')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Task ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('projectModule.project.name')
                    ->label('Project Name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('projectModule.name')
                    ->label('Project Module')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Task Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Task Description')
                    ->limit(50),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('evaluate')
                    ->label('Evaluate')
                    ->icon('heroicon-o-star')
                    ->modalHeading(fn (Task $record): string => "Evaluate: {$record->name}")
                    ->modalSubmitActionLabel('Save Evaluation')
                    ->modalIcon('heroicon-o-star')
                    ->form([
                        Forms\Components\Section::make('Module Information')
                            ->schema([
                                Forms\Components\TextInput::make('module_weight')
                                    ->label('Module Weight')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->default(function (Task $record) {
                                        return $record->projectModule->weight ?? 'N/A';
                                    }),
                            ]),
                        Forms\Components\Section::make('RICE Weight')
                            ->schema([
                                Forms\Components\Grid::make(4)
                                    ->schema([
                                        Forms\Components\Select::make('input1')
                                            ->label('Reach (R)')
                                            ->options([1 => 1, 3 => 3, 4 => 4, 6 => 6, 8 => 8, 10 => 10])
                                            ->required(),
                                        Forms\Components\Select::make('input2')
                                            ->label('Impact (I)')
                                            ->options([1 => 1, 3 => 3, 4 => 4, 6 => 6, 8 => 8, 10 => 10])
                                            ->required(),
                                        Forms\Components\Select::make('input3')
                                            ->label('Confidence (C)')
                                            ->options([1 => 1, 3 => 3, 4 => 4, 6 => 6, 8 => 8, 10 => 10])
                                            ->required(),
                                        Forms\Components\Select::make('input4')
                                            ->label('Effort (E)')
                                            ->options([1 => 1, 3 => 3, 5 => 5, 7 => 7, 10 => 10])
                                            ->required(),
                                    ]),
                            ]),
                        Forms\Components\Section::make('ISO QAs Weight')
                        ->schema([
                            Forms\Components\Grid::make(3)
                                ->schema(
                                    IsoTask::take(9)->get()->map(function ($isoTask) {
                                        return Forms\Components\Select::make("matrix_values.{$isoTask->id}")
                                            ->label($isoTask->name)
                                            ->options([0 => 0, 1 => 1, 2 => 2, 3 => 3, 5 => 5])
                                            ->required()
                                            ->extraAttributes(['class' => 'text-center']);
                                    })->toArray()
                                ),
                            ]),
                    ])
                    ->action(function (Task $record, array $data) {
                        DB::transaction(function () use ($record, $data) {
                            $calculatedValue = ($data['input1'] * $data['input2'] * $data['input3']) / $data['input4'];

                            // Calculate matrix_calculated_value
                            $matrixCalculatedValue = 0;
                            foreach ($data['matrix_values'] as $isoTaskId => $value) {
                                $isoTask = IsoTask::find($isoTaskId);
                                $matrixCalculatedValue += ($isoTask->weight / 100) * $value; // Convert weight to decimal
                            }

                            $metric = new Metric([
                                'task_id' => $record->id,
                                'user_id' => auth()->id(),
                                'module_weight' => $record->projectModule->weight,
                                'input1' => $data['input1'],
                                'input2' => $data['input2'],
                                'input3' => $data['input3'],
                                'input4' => $data['input4'],
                                'calculated_value' => $calculatedValue,
                                'matrix_values' => $data['matrix_values'],
                                'matrix_calculated_value' => $matrixCalculatedValue,
                            ]);

                            $record->metrics()->save($metric);
                            $record->update(['state' => TaskState::DONE]);

                            // Calculate average of user evaluations
                            $averageEvaluation = $record->metrics()->avg('calculated_value');
                            $userCount = $record->metrics()->count();

                            // Update the task with the new average
                            $record->update([
                                'average_evaluation' => $averageEvaluation,
                                'evaluation_count' => $userCount,
                            ]);

                            Log::info('Metric saved and average updated for task: ' . $record->id);
                        });

                        Notification::make()
                            ->success()
                            ->title('Evaluation added successfully')
                            ->body('Your evaluation has been added and the task average has been updated.')
                            ->send();
                    })
                    ->visible(function (Task $record) {
                        $isBeforeEndDate = $record->end_date ? Carbon::now()->lt($record->end_date) : true;
                        $userHasNotEvaluated = !$record->metrics()->where('user_id', Auth::id())->exists();
                        return $isBeforeEndDate && $userHasNotEvaluated;
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('state', TaskState::REPO)
            ->withCount(['metrics' => function ($query) {
                $query->where('user_id', Auth::id());
            }])
            ->with(['metrics', 'projectModule']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRepos::route('/'),
            'create' => Pages\CreateRepo::route('/create'),
            'edit' => Pages\EditRepo::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return 'Repo Task';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Repo Tasks';
    }
}
