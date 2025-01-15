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

    public static function form(Forms\Form $form): Forms\Form
    {
        $isAdmin = auth()->user()->is_admin;

        return $form
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
                    ])
                    ->visible($isAdmin),

                // RICE Evaluation Settings - Admin Only
                Forms\Components\Section::make('RICE Evaluation Settings')
                    ->schema([
                        Forms\Components\DateTimePicker::make('rice_evaluation_end_time')
                            ->label('Admin RICE Evaluation End Time')
                            ->required()
                            ->helperText('After this time, the final RICE score will be calculated from admin evaluations')
                            ->visible(fn ($record) => !$record?->rice_score),

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
                    ->visible($isAdmin),

                Forms\Components\Section::make('Final RICE Score')
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
                            ])
                    ])
                    ->visible($isAdmin && fn (string $operation) => $operation === 'edit'),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        $isAdmin = auth()->user()->is_admin;

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
                        'approved' => 'warning',
                        'completed' => 'success',
                        default => 'gray',
                    }),

                // Admin-only columns
                Tables\Columns\TextColumn::make('sourceGroup.name')
                    ->searchable()
                    ->sortable()
                    ->visible($isAdmin),

                Tables\Columns\TextColumn::make('source.name')
                    ->searchable()
                    ->sortable()
                    ->visible($isAdmin),

                Tables\Columns\TextColumn::make('rice_evaluation_remaining_time')
                    ->label('RICE Evaluation Time')
                    ->badge()
                    ->color(fn ($state) => $state === 'Ended' ? 'danger' : 'success')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('rice_evaluation_end_time', $direction);
                    })
                    ->visible($isAdmin),

                Tables\Columns\TextColumn::make('user_evaluation_remaining_time')
                    ->label('User Evaluation Time')
                    ->badge()
                    ->color(fn ($state) => $state === 'Ended' ? 'danger' : 'success')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('evaluation_end_time', $direction);
                    })
                    ->visible($isAdmin),

                Tables\Columns\TextColumn::make('rice_score')
                    ->numeric(2)
                    ->sortable()
                    ->visible($isAdmin),

                Tables\Columns\TextColumn::make('overall_evaluation_value')
                    ->numeric(2)
                    ->sortable()
                    ->visible($isAdmin),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'evaluating' => 'Evaluating',
                        'completed' => 'Completed',
                    ]),
                Tables\Filters\SelectFilter::make('project')
                    ->relationship('project', 'name'),
                Tables\Filters\SelectFilter::make('source_group')
                    ->relationship('sourceGroup', 'name')
                    ->visible($isAdmin),
                Tables\Filters\SelectFilter::make('source')
                    ->relationship('source', 'name')
                    ->visible($isAdmin),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('evaluate_rice')
                    ->form([
                        Forms\Components\Grid::make(4)
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
                    ->action(function (array $data, Task $record): void {
                        $record->riceEvaluations()->create([
                            'user_id' => auth()->id(),
                            'reach' => $data['reach'],
                            'impact' => $data['impact'],
                            'confidence' => $data['confidence'],
                            'effort' => $data['effort'],
                        ]);

                        $record->calculateFinalRiceScore();
                    })
                    ->visible(fn (Task $record): bool => $record->canBeEvaluatedByAdmin() && auth()->user()->is_admin)
                    ->modalHeading('Evaluate RICE Score')
                    ->icon('heroicon-o-calculator'),

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
                        auth()->user()->is_admin &&
                        $record->rice_evaluation_end_time &&
                        $record->rice_evaluation_end_time->isFuture()
                    ),

                Tables\Actions\Action::make('force_evaluation_end')
                    ->label('End Evaluation')
                    ->icon('heroicon-o-clock')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Task $record) {
                        $record->update([
                            'evaluation_end_time' => now()
                        ]);
                        $record->calculateOverallEvaluation();
                        Notification::make()
                            ->success()
                            ->title('User evaluation period has been ended')
                            ->send();
                    })
                    ->visible(fn (Task $record) =>
                        auth()->user()->is_admin &&
                        $record->evaluation_end_time &&
                        $record->evaluation_end_time->isFuture()
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible($isAdmin),
                ]),
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
}
