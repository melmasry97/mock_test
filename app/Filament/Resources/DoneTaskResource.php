<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\Task;
use Filament\Tables;
use App\Enums\TaskState;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\ProjectModule;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\DoneTaskResource\Pages;

class DoneTaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

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

                Tables\Columns\TextColumn::make('name')
                    ->label('Task Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('projectModule.project.name')
                    ->label('Project')
                    ->sortable(),

                Tables\Columns\TextColumn::make('projectModule.name')
                    ->label('Module')
                    ->sortable(),

                Tables\Columns\TextColumn::make('sourceGroup.name')
                    ->label('Req Source Group')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('source.name')
                    ->label('Req Source')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('type.name')
                    ->label('ReqType')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('projectModule.weight')
                    ->label('Average Module Weight')
                    ->numeric(2)
                    ->sortable(),

                Tables\Columns\TextColumn::make('categories_avg_weight')
                    ->label('Type Categories Weight')
                    ->numeric(2)
                    ->getStateUsing(function (Task $record) {
                        return $record->categories()
                            ->join('project_type_category_evaluations', function($join) use ($record) {
                                $join->on('type_categories.id', '=', 'project_type_category_evaluations.category_id')
                                    ->where('project_type_category_evaluations.project_id', '=', $record->project_id);
                            })
                            ->avg('project_type_category_evaluations.weight') ?? 0;
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('rice_score')
                    ->label('RICE Score')
                    ->numeric(2)
                    ->sortable(),

                Tables\Columns\TextColumn::make('overall_evaluation_value')
                    ->label('Average Team Weight')
                    ->numeric(2)
                    ->sortable(),

                Tables\Columns\TextColumn::make('weight')
                    ->label('Final Weight')
                    ->numeric(2)
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => $state === 'completed' ? 'Ready for Dev' : $state)
                    ->color('success'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('project')
                    ->relationship('projectModule.project', 'name'),
                Tables\Filters\SelectFilter::make('module')
                    ->relationship('projectModule', 'name'),
                Tables\Filters\SelectFilter::make('source_group')
                    ->relationship('sourceGroup', 'name')
                    ->label('Source Group')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('source')
                    ->relationship('source', 'name')
                    ->label('Source')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('type')
                    ->relationship('type', 'name')
                    ->label('Type')
                    ->searchable()
                    ->preload(),
            ])
            ->defaultSort('weight', 'desc');
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
            'index' => Pages\ListDoneTasks::route('/'),
            'create' => Pages\CreateDoneTask::route('/create'),
            'edit' => Pages\EditDoneTask::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('status', 'completed');
    }

    public static function getModelLabel(): string
    {
        return 'Backlog Task';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Backlog Tasks';
    }
}
