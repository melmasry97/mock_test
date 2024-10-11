<?php

namespace App\Filament\User\Resources;

use Filament\Forms;
use App\Models\Task;
use Filament\Tables;
use App\Enums\TaskState;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\ProjectModule;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Filament\User\Resources\BacklogResource\Pages;

class BacklogResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-queue-list';

    protected static ?string $navigationLabel = 'Backlog Tasks';

    protected static ?string $navigationGroup = 'Task Management';

    protected static ?string $slug = 'backlog-tasks';

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
                    ->default(TaskState::DONE),
                Forms\Components\TextInput::make('weight')
                    ->label('Weight (%)')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->maxValue(100),
                Forms\Components\Select::make('project_module_id')
                    ->label('Project Module')
                    ->options(ProjectModule::all()->pluck('name', 'id'))
                    ->searchable()
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
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                // Remove bulk actions if not needed
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
            'index' => Pages\ListBacklogTasks::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('state', TaskState::DONE);
    }

    public static function getModelLabel(): string
    {
        return 'Backlog Task';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Backlog Tasks';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }
}
