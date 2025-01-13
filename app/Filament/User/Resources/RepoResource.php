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
                                Forms\Components\TextInput::make('rice_score')
                                    ->label('RICE Score')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->default(function (Task $record) {
                                        return $record->rice_score ?? 'N/A';
                                    }),
                            ]),
                        Forms\Components\Section::make('Evaluation')
                            ->schema([
                                Forms\Components\Select::make('fibonacci_weight')
                                    ->label('Fibonacci Weight')
                                    ->options([
                                        1 => '1 - Very Low',
                                        2 => '2 - Low',
                                        3 => '3 - Low Medium',
                                        5 => '5 - Medium',
                                        8 => '8 - Medium High',
                                        13 => '13 - High',
                                        21 => '21 - Very High'
                                    ])
                                    ->required()
                                    ->helperText('Choose a Fibonacci number to evaluate this task'),
                            ])
                    ])
                    ->action(function (Task $record, array $data) {
                        DB::transaction(function () use ($record, $data) {
                            $taskEvaluation = new TaskEvaluation([
                                'task_id' => $record->id,
                                'user_id' => auth()->id(),
                                'fibonacci_weight' => $data['fibonacci_weight'],
                            ]);

                            $taskEvaluation->save();
                            $record->calculateOverallEvaluation();
                            $record->update(['state' => TaskState::DONE]);

                            Log::info('Task evaluation saved for task: ' . $record->id);
                        });

                        Notification::make()
                            ->success()
                            ->title('Evaluation added successfully')
                            ->body('Your evaluation has been added and the task has been updated.')
                            ->send();
                    })
                    ->visible(function (Task $record) {
                        $isBeforeEndDate = $record->end_date ? Carbon::now()->lt($record->end_date) : true;
                        $userHasNotEvaluated = !$record->evaluations()->where('user_id', Auth::id())->exists();
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
