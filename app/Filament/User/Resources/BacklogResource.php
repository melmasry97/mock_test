<?php

namespace App\Filament\User\Resources;

use Filament\Forms;
use App\Models\Task;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\User\Resources\BacklogResource\Pages\EditBacklogTask;
use App\Filament\User\Resources\BacklogResource\Pages\ListBacklogTasks;
use App\Filament\User\Resources\BacklogResource\Pages\CreateBacklogTask;

class BacklogResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationLabel = 'Backlog';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('evaluations', function ($query) {
                $query->where('user_id', auth()->id());
            });
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

                Tables\Columns\TextColumn::make('user_evaluation')
                    ->label('Your Evaluation')
                    ->badge()
                    ->color('success')
                    ->formatStateUsing(function ($record) {
                        $evaluation = $record->evaluations()
                            ->where('user_id', auth()->id())
                            ->first();

                        return $evaluation ? $evaluation->fibonacci_weight : 'N/A';
                    }),

                Tables\Columns\TextColumn::make('overall_evaluation_value')
                    ->label('Overall Score')
                    ->numeric(2)
                    ->sortable(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBacklogTasks::route('/'),
            'create' => CreateBacklogTask::route('/create'),
            'edit' => EditBacklogTask::route('/{record}/edit'),
        ];
    }
}
