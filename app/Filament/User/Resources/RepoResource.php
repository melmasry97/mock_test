<?php

namespace App\Filament\User\Resources;

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
use App\Filament\User\Resources\RepoResource\Pages;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;

class RepoResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationLabel = 'Repos';

    protected static ?string $navigationGroup = 'Repo Management';

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
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->limit(50),
                Tables\Columns\TextColumn::make('weight')
                    ->label('Weight (%)')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('projectModule.name')
                    ->label('Project Module')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('Evaluate')
                    ->form([
                        Forms\Components\Section::make('Score Weight')
                            ->schema([
                                Forms\Components\Grid::make(4)
                                    ->schema([
                                        Forms\Components\Select::make('input1')
                                            ->label('Input 1')
                                            ->options([1 => 1, 3 => 3, 4 => 4, 6 => 6, 8 => 8, 10 => 10])
                                            ->required(),
                                        Forms\Components\Select::make('input2')
                                            ->label('Input 2')
                                            ->options([1 => 1, 3 => 3, 4 => 4, 6 => 6, 8 => 8, 10 => 10])
                                            ->required(),
                                        Forms\Components\Select::make('input3')
                                            ->label('Input 3')
                                            ->options([1 => 1, 3 => 3, 4 => 4, 6 => 6, 8 => 8, 10 => 10])
                                            ->required(),
                                        Forms\Components\Select::make('input4')
                                            ->label('Input 4')
                                            ->options([1 => 1, 3 => 3, 5 => 5, 7 => 7, 10 => 10])
                                            ->required(),
                                    ]),
                            ]),
                        Forms\Components\Section::make('ISO Weight')
                            ->schema([
                                Forms\Components\Grid::make(3)
                                    ->schema(
                                        IsoTask::take(9)->get()->map(function ($isoTask) {
                                            return Forms\Components\Select::make("matrix_values.{$isoTask->id}")
                                                ->label($isoTask->name)
                                                ->options([0 => 0, 1 => 1, 2 => 2, 3 => 3, 5 => 5])
                                                ->required()
                                                ->extraAttributes(['class' => 'text-center']);
                                        })->toArray()
                                    ),
                            ]),
                    ])
                    ->action(function (Task $record, array $data) {
                        DB::transaction(function () use ($record, $data) {
                            $calculatedValue = ($data['input1'] * $data['input2'] * $data['input3']) / $data['input4'];

                            // Calculate matrix_calculated_value
                            $matrixCalculatedValue = 0;
                            foreach ($data['matrix_values'] as $isoTaskId => $value) {
                                $isoTask = IsoTask::find($isoTaskId);
                                $matrixCalculatedValue += ($isoTask->weight / 100) * $value; // Convert weight to decimal
                            }

                            $metric = new Metric([
                                'task_id' => $record->id,
                                'user_id' => auth()->id(),
                                'module_weight' => $record->projectModule->weight,
                                'input1' => $data['input1'],
                                'input2' => $data['input2'],
                                'input3' => $data['input3'],
                                'input4' => $data['input4'],
                                'calculated_value' => $calculatedValue,
                                'matrix_values' => $data['matrix_values'],
                                'matrix_calculated_value' => $matrixCalculatedValue,
                            ]);

                            $record->metrics()->save($metric);
                            $record->update(['state' => TaskState::DONE]);

                            Log::info('Metric saved for task: ' . $record->id);
                        });

                        Notification::make()
                            ->success()
                            ->title('Metrics added successfully')
                            ->body('The metrics have been added and the task has been marked as done.')
                            ->send();
                    })
                    ->modalHeading('Evaluate Repo Task')
                    ->modalButton('Submit'),
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

    public static function getModelLabel(): string
    {
        return 'Repo Task';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Repo Tasks';
    }
}
