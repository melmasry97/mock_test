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

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('state')
                            ->options(TaskState::getLabels())
                            ->required()
                            ->enum(TaskState::class),
                    ]),

                Forms\Components\Textarea::make('description')
                    ->maxLength(65535)
                    ->columnSpanFull(),

                // Project Section
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\Select::make('project_id')
                            ->label('Project')
                            ->options(Project::pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->live()
                            ->required()
                            ->afterStateUpdated(fn (callable $set) => $set('project_module_id', null)),

                        Forms\Components\Select::make('project_module_id')
                            ->label('Project Module')
                            ->options(function (callable $get) {
                                $projectId = $get('project_id');
                                if (!$projectId) {
                                    return [];
                                }
                                return ProjectModule::where('project_id', $projectId)
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->disabled(fn (callable $get) => !$get('project_id')),
                    ]),

                // Source Section
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\Select::make('source_group_id')
                            ->relationship('sourceGroup', 'name')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->label('Source Group')
                            ->afterStateUpdated(fn (callable $set) => $set('source_id', null)),

                        Forms\Components\Select::make('source_id')
                            ->options(function (callable $get) {
                                $groupId = $get('source_group_id');
                                if (!$groupId) {
                                    return [];
                                }
                                return Source::where('source_group_id', $groupId)
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->live()
                            ->label('Source')
                            ->disabled(fn (callable $get) => !$get('source_group_id')),
                    ]),

                // Type and Status Section
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\Select::make('type_id')
                            ->relationship('type', 'name')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->label('Type')
                            ->afterStateUpdated(fn (callable $set) => $set('categories', [])),

                        Forms\Components\Select::make('categories')
                            ->relationship(
                                'categories',
                                'name',
                                function (Builder $query, callable $get) {
                                    $typeId = $get('type_id');
                                    $query->where('average_value', '>', 0);

                                    if ($typeId) {
                                        $query->where('type_id', $typeId);
                                    }

                                    return $query;
                                }
                            )
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->label('Type Categories')
                            ->helperText('Select from evaluated categories only')
                            ->disabled(fn (callable $get) => !$get('type_id')),

                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'approved' => 'Approved',
                                'evaluating' => 'Evaluating',
                                'completed' => 'Completed',
                            ])
                            ->default('pending'),
                    ]),

                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('task_evaluation_time_period')
                            ->numeric()
                            ->label('Evaluation Time Period (days)')
                            ->nullable(),

                        Forms\Components\DateTimePicker::make('evaluation_end_time')
                            ->label('Evaluation End Time')
                            ->nullable(),
                    ]),

                // RICE Section at the end
                Forms\Components\Section::make('RICE Weight')
                    ->collapsible()
                    ->schema([
                        Forms\Components\Grid::make(4)
                            ->schema([
                                Forms\Components\Select::make('reach')
                                    ->label('Reach (R)')
                                    ->options([1 => 1, 3 => 3, 4 => 4, 6 => 6, 8 => 8, 10 => 10])
                                    ->required()
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        $rice_score = ($state * $get('impact') * $get('confidence')) / ($get('effort') ?: 1);
                                        $set('rice_score', round($rice_score, 2));
                                    }),

                                Forms\Components\Select::make('impact')
                                    ->label('Impact (I)')
                                    ->options([1 => 1, 3 => 3, 4 => 4, 6 => 6, 8 => 8, 10 => 10])
                                    ->required()
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        $rice_score = ($get('reach') * $state * $get('confidence')) / ($get('effort') ?: 1);
                                        $set('rice_score', round($rice_score, 2));
                                    }),

                                Forms\Components\Select::make('confidence')
                                    ->label('Confidence (C)')
                                    ->options([1 => 1, 3 => 3, 4 => 4, 6 => 6, 8 => 8, 10 => 10])
                                    ->required()
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        $rice_score = ($get('reach') * $get('impact') * $state) / ($get('effort') ?: 1);
                                        $set('rice_score', round($rice_score, 2));
                                    }),

                                Forms\Components\Select::make('effort')
                                    ->label('Effort (E)')
                                    ->options([1 => 1, 3 => 3, 5 => 5, 7 => 7, 10 => 10])
                                    ->required()
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        $rice_score = ($get('reach') * $get('impact') * $get('confidence')) / ($state ?: 1);
                                        $set('rice_score', round($rice_score, 2));
                                    }),
                            ]),

                        Forms\Components\TextInput::make('rice_score')
                            ->label('RICE Score')
                            ->disabled()
                            ->dehydrated(),
                    ]),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('state')
                    ->badge()
                    ->color(fn (TaskState $state): string => match ($state) {
                        TaskState::REPO => 'gray',
                        TaskState::TODO => 'warning',
                        TaskState::IN_PROGRESS => 'info',
                        TaskState::DONE => 'success',
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'approved' => 'warning',
                        'evaluating' => 'info',
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

                Tables\Columns\TextColumn::make('sourceGroup.name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('source.name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('type.name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('rice_score')
                    ->numeric(2)
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('overall_evaluation_value')
                    ->numeric(2)
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('state')
                    ->options(TaskState::getLabels()),
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
                    ->relationship('sourceGroup', 'name'),
                Tables\Filters\SelectFilter::make('source')
                    ->relationship('source', 'name'),
                Tables\Filters\SelectFilter::make('type')
                    ->relationship('type', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'edit' => Pages\EditTask::route('/{record}/edit'),
        ];
    }
}
