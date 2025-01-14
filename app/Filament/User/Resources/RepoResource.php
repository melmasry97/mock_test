<?php

namespace App\Filament\User\Resources;

use Filament\Forms;
use App\Models\Task;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\User\Resources\RepoResource\Pages\ListRepos;

class RepoResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-stack';

    protected static ?string $navigationLabel = 'Repository';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('status', 'approved')
            ->whereDoesntHave('evaluations', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->whereNotNull('evaluation_end_time')
            ->where('evaluation_end_time', '>', now());
    }

    public static function table(Table $table): Table
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

                Tables\Columns\TextColumn::make('type.name')
                    ->searchable()
                    ->sortable()
                    ->label('Type'),

                Tables\Columns\TextColumn::make('user_evaluation_remaining_time')
                    ->label('Time Remaining')
                    ->badge()
                    ->color(fn ($state) => $state === 'Ended' ? 'danger' : 'success')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('evaluation_end_time', $direction);
                    }),
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
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRepos::route('/'),
        ];
    }
}
