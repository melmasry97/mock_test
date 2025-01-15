<?php

namespace App\Filament\Resources;

use App\Enums\TaskState;
use App\Filament\Resources\TaskResource\Pages;
use App\Models\Task;
use App\Models\TypeCategory;
use App\Models\Project;
use App\Models\ProjectModule;
use App\Models\Source;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Requirement Tasks';

    protected static ?string $navigationLabel = 'Created Tasks (Waiting for RICE & Admin Approval)';

    protected static ?int $navigationSort = 2;

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\Select::make('project_id')
                            ->relationship('project', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('Project'),

                        Forms\Components\Select::make('project_module_id')
                            ->relationship('projectModule', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('Project Module'),
                    ]),

                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Textarea::make('description')
                    ->maxLength(65535)
                    ->columnSpanFull(),

                // Admin-only fields
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\Select::make('source_group_id')
                            ->relationship('sourceGroup', 'name')
                            ->searchable()
                            ->preload()
                            ->label('Source Group'),

                        Forms\Components\Select::make('source_id')
                            ->relationship('source', 'name')
                            ->searchable()
                            ->preload()
                            ->label('Source'),

                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'evaluating' => 'Evaluating',
                                'approved' => 'Approved',
                                'completed' => 'Completed',
                            ])
                            ->default('pending')
                            ->required(),

                        Forms\Components\DateTimePicker::make('evaluation_end_time')
                            ->label('User Evaluation End Time')
                            ->helperText('Users can only evaluate the task before this time'),

                        Forms\Components\DateTimePicker::make('rice_evaluation_end_time')
                            ->label('Admin RICE Evaluation End Time')
                            ->helperText('After this time, the final RICE score will be calculated from admin evaluations'),
                    ])
                    ->visible(fn() => auth()->user()->isAdmin())
                    ->columnSpanFull(),

                // RICE Evaluation Settings - Admin Only
                Forms\Components\Section::make('RICE Evaluation Settings')
                    ->schema([
                        Forms\Components\Grid::make(5)
                            ->schema([
                                Forms\Components\TextInput::make('reach')
                                    ->numeric()
                                    ->disabled()
                                    ->label('R'),

                                Forms\Components\TextInput::make('impact')
                                    ->numeric()
                                    ->disabled()
                                    ->label('I'),

                                Forms\Components\TextInput::make('confidence')
                                    ->numeric()
                                    ->disabled()
                                    ->label('C'),

                                Forms\Components\TextInput::make('effort')
                                    ->numeric()
                                    ->disabled()
                                    ->label('E'),

                                Forms\Components\TextInput::make('rice_score')
                                    ->numeric()
                                    ->disabled()
                                    ->label('Score'),
                            ]),

                        Forms\Components\Placeholder::make('rice_evaluations')
                            ->label('RICE Evaluations')
                            ->content(function ($record) {
                                if (!$record) return 'Save the task first to view evaluations.';

                                $evaluations = $record->riceEvaluations()->with('user')->get();
                                if ($evaluations->isEmpty()) return 'No evaluations yet.';

                                return view('filament.components.rice-evaluations-table', [
                                    'evaluations' => $evaluations->map(function ($eval) {
                                        return [
                                            'evaluator' => $eval->user->name,
                                            'reach' => $eval->reach,
                                            'impact' => $eval->impact,
                                            'confidence' => $eval->confidence,
                                            'effort' => $eval->effort,
                                            'score' => $eval->score,
                                            'evaluated_at' => $eval->created_at->format('Y-m-d H:i:s'),
                                        ];
                                    })
                                ]);
                            })
                            ->visible(fn (string $operation) => $operation === 'edit'),
                    ])
                    ->visible(fn() => auth()->user()->isAdmin())
                    ->columnSpanFull(),

                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('overall_evaluation_value')
                            ->numeric()
                            ->disabled()
                            ->label('Overall Evaluation Score'),

                        Forms\Components\TextInput::make('weight')
                            ->numeric()
                            ->disabled()
                            ->label('Final Weight'),
                    ])
                    ->visible(fn() => auth()->user()->isAdmin())
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('project.name')
                    ->searchable()
                    ->sortable()
                    ->label('Project'),

                Tables\Columns\TextColumn::make('projectModule.name')
                    ->searchable()
                    ->sortable()
                    ->label('Module'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'evaluating' => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('rice_evaluation_remaining_time')
                    ->label('RICE Time Remaining')
                    ->badge()
                    ->color(fn ($state) => $state === 'Ended' ? 'danger' : 'warning'),

                Tables\Columns\TextColumn::make('rice_score')
                    ->label('RICE Score')
                    ->numeric(2)
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'evaluating' => 'Evaluating',
                    ])
                    ->default('pending'),
                Tables\Filters\SelectFilter::make('project')
                    ->relationship('project', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('evaluate_rice')
                    ->label('RICE Evaluate')
                    ->icon('heroicon-o-star')
                    ->form([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('reach')
                                    ->label('Reach (R)')
                                    ->options([1 => 1, 3 => 3, 4 => 4, 6 => 6, 8 => 8, 10 => 10])
                                    ->required(),
                                Forms\Components\Select::make('impact')
                                    ->label('Impact (I)')
                                    ->options([1 => 1, 3 => 3, 4 => 4, 6 => 6, 8 => 8, 10 => 10])
                                    ->required(),
                                Forms\Components\Select::make('confidence')
                                    ->label('Confidence (C)')
                                    ->options([1 => 1, 3 => 3, 4 => 4, 6 => 6, 8 => 8, 10 => 10])
                                    ->required(),
                                Forms\Components\Select::make('effort')
                                    ->label('Effort (E)')
                                    ->options([1 => 1, 3 => 3, 5 => 5, 7 => 7, 10 => 10])
                                    ->required(),
                            ]),
                    ])
                    ->action(function (Task $record, array $data): void {
                        $record->riceEvaluations()->create([
                            'user_id' => auth()->id(),
                            ...array_map('intval', $data)
                        ]);
                    })
                    ->visible(fn (Task $record) =>
                        $record->canBeEvaluatedByAdmin() &&
                        !$record->riceEvaluations()->where('user_id', auth()->id())->exists()
                    ),
                Tables\Actions\Action::make('force_rice_end')
                    ->label('End RICE')
                    ->icon('heroicon-o-clock')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Task $record) {
                        $record->update([
                            'rice_evaluation_end_time' => now()
                        ]);
                        $record->calculateFinalRiceScore();
                        Notification::make()
                            ->success()
                            ->title('RICE evaluation period has been ended')
                            ->send();
                    })
                    ->visible(fn (Task $record) =>
                        $record->rice_evaluation_end_time &&
                        $record->rice_evaluation_end_time->isFuture()
                    ),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'edit' => Pages\EditTask::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereIn('status', ['pending', 'evaluating']);
    }
}
