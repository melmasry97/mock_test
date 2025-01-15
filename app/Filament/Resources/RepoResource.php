<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\Task;
use Filament\Tables;
use App\Enums\TaskState;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\ProjectModule;
use App\Models\Metric;
use App\Models\IsoTask;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\RepoResource\Pages;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class RepoResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Approved Tasks';

    protected static ?string $navigationGroup = 'Requirement Tasks';

    protected static ?int $navigationSort = 3;

    protected static ?string $slug = 'repos';

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
                Tables\Actions\EditAction::make(),
                // Evaluation button removed
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListRepos::route('/'),
            'create' => Pages\CreateRepo::route('/create'),
            'edit' => Pages\EditRepo::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('state', TaskState::REPO);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }
}
