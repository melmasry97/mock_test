<?php

namespace App\Filament\User\Resources;

use Filament\Forms;
use App\Models\Task;
use Filament\Tables;
use App\Models\IsoTask;
use App\Enums\TaskState;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\User\Resources\TaskResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\User\Resources\TaskResource\RelationManagers;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('end_date')
                    ->label('End Date')
                    ->required(),
                // Weight input removed
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'evaluating' => 'info',
                        'approved' => 'warning',
                        'completed' => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('project.name')
                    ->searchable()
                    ->sortable()
                    ->label('Project'),

                Tables\Columns\TextColumn::make('projectModule.name')
                    ->searchable()
                    ->sortable()
                    ->label('Module'),

                Tables\Columns\TextColumn::make('type.name')
                    ->searchable()
                    ->sortable()
                    ->label('Type'),

                Tables\Columns\TextColumn::make('user_evaluation_remaining_time')
                    ->label('Evaluation Time')
                    ->badge()
                    ->color(fn ($state) => $state === 'Ended' ? 'danger' : 'success')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('evaluation_end_time', $direction);
                    }),

                Tables\Columns\TextColumn::make('user_evaluation_status')
                    ->label('Your Evaluation')
                    ->badge()
                    ->formatStateUsing(function ($record) {
                        $evaluation = $record->evaluations()
                            ->where('user_id', auth()->id())
                            ->first();

                        if ($evaluation) {
                            return 'Evaluated: ' . $evaluation->fibonacci_weight;
                        }

                        return 'Not Evaluated';
                    })
                    ->color(function ($record) {
                        return $record->evaluations()
                            ->where('user_id', auth()->id())
                            ->exists() ? 'success' : 'warning';
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('evaluate')
                    ->form([
                        Forms\Components\Grid::make(1)
                            ->schema([
                                Forms\Components\Select::make('fibonacci_weight')
                                    ->label('Evaluation Weight')
                                    ->options([
                                        1 => '1 - Very Low',
                                        2 => '2 - Low',
                                        3 => '3 - Medium',
                                        5 => '5 - High',
                                        8 => '8 - Very High',
                                        13 => '13 - Critical'
                                    ])
                                    ->required(),
                            ]),
                    ])
                    ->action(function (array $data, Task $record): void {
                        $record->evaluations()->create([
                            'user_id' => auth()->id(),
                            'fibonacci_weight' => $data['fibonacci_weight'],
                        ]);

                        $record->calculateOverallEvaluation();
                    })
                    ->visible(fn (Task $record): bool => $record->canBeEvaluatedByUser())
                    ->modalHeading('Evaluate Task')
                    ->icon('heroicon-o-star'),
            ])
            ->bulkActions([
                //
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
