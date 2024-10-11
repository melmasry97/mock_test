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
                //
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
                Tables\Columns\TextColumn::make('end_date')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('state')
                    ->options([
                        'all' => 'All Tasks',
                        TaskState::REPO->value => 'Repo Tasks',
                        TaskState::DONE->value => 'Done Tasks',
                    ])
                    ->default('all')
                    ->query(function ($query, array $data) {
                        if ($data['value'] === 'all') {
                            return $query;
                        }
                        return $query->where('state', $data['value']);
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('evaluate')
                    ->label('Evaluate')
                    ->icon('heroicon-o-star')
                    ->modalHeading('Evaluate Task')
                    ->modalSubmitActionLabel('Save Evaluation')
                    ->modalIcon('heroicon-o-star')
                    ->form([
                        Forms\Components\Section::make('Module Information')
                            ->schema([
                                Forms\Components\TextInput::make('module_weight')
                                    ->label('Module Weight')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->default(function (Task $record) {
                                        return $record->projectModule->weight ?? 'N/A';
                                    }),
                            ]),
                        Forms\Components\Section::make('RICE Weight')
                            ->schema([
                                Forms\Components\Grid::make(4)
                                    ->schema([
                                        Forms\Components\Select::make('input1')
                                            ->label('Reach (R)')
                                            ->options([1 => 1, 3 => 3, 4 => 4, 6 => 6, 8 => 8, 10 => 10])
                                            ->required(),
                                        Forms\Components\Select::make('input2')
                                            ->label('Impact (I)')
                                            ->options([1 => 1, 3 => 3, 4 => 4, 6 => 6, 8 => 8, 10 => 10])
                                            ->required(),
                                        Forms\Components\Select::make('input3')
                                            ->label('Confidence (C)')
                                            ->options([1 => 1, 3 => 3, 4 => 4, 6 => 6, 8 => 8, 10 => 10])
                                            ->required(),
                                        Forms\Components\Select::make('input4')
                                            ->label('Effort (E)')
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
                            ->title('Evaluation added successfully')
                            ->body('The evaluation has been added and the task has been marked as done.')
                            ->send();
                    })
                    ->visible(function (Task $record) {
                        $isStateValid = $record->state === TaskState::REPO || $record->state === TaskState::IN_PROGRESS;
                        $isBeforeEndDate = !$record->end_date || Carbon::now()->lt($record->end_date);
                        $userHasNotEvaluated = !$record->metrics()->where('user_id', auth()->id())->exists();
                        return $isStateValid && $isBeforeEndDate && $userHasNotEvaluated;
                    }),
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
