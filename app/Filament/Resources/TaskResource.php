<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\Task;
use Filament\Tables;
use App\Models\Metric;
use App\Enums\TaskState;
use App\Models\ProjectModule;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;
use App\Filament\Resources\TaskResource\Pages;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard'; // Changed this line

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->maxLength(65535),
                Forms\Components\Select::make('state')
                    ->options(TaskState::getLabels())
                    ->enum(TaskState::class)
                    ->required(),
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

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')  // Changed 'title' to 'name'
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->limit(50),
                Tables\Columns\TextColumn::make('state')
                    ->formatStateUsing(fn (TaskState $state): string => TaskState::getLabels()[$state->value])
                    ->sortable(),
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
                Tables\Actions\Action::make('Add Metrics')
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
                                    ->schema(array_map(fn ($i) =>
                                        Forms\Components\Select::make("matrixValues.{$i}")
                                            ->label('Value ' . ($i + 1))
                                            ->options([0 => 0, 1 => 1, 2 => 2, 3 => 3, 5 => 5])
                                            ->required()
                                            ->extraAttributes(['class' => 'text-center']),
                                        range(0, 8)
                                    )),
                            ]),
                    ])
                    ->action(function (Task $record, array $data) {
                        DB::transaction(function () use ($record, $data) {
                            $calculatedValue = ($data['input1'] * $data['input2'] * $data['input3']) / $data['input4'];

                            $metric = new Metric([
                                'task_id' => $record->id,
                                'user_id' => auth()->id(), // Add the authenticated user's ID
                                'module_weight' => $record->projectModule->weight,
                                'input1' => $data['input1'],
                                'input2' => $data['input2'],
                                'input3' => $data['input3'],
                                'input4' => $data['input4'],
                                'calculated_value' => $calculatedValue,
                                'matrix_values' => json_encode($data['matrixValues']),
                            ]);

                            // Ensure the metrics relationship is defined in the Task model
                            $record->metrics()->save($metric);  // Ensure this relationship is valid
                            // Check for any validation or exception handling if needed
                            $record->update(['state' => TaskState::DONE]);

                            Log::info('Metric saved for task: ' . $record->id);
                        });

                        Notification::make()
                            ->success()
                            ->title('Metrics added successfully')
                            ->body('The metrics have been added and the task has been marked as done.')
                            ->send();
                    })
                    ->visible(fn (Task $record) => $record->state === TaskState::REPO || $record->state === TaskState::IN_PROGRESS)
                    ->modalHeading('Add Metrics for Task')
                    ->modalButton('Submit')
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Metrics added successfully')
                            ->body('The metrics have been added and the task has been marked as done.')
                    ),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
