<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use App\Models\IsoTask;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Rules\IsoTaskWeightRule;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\CreateAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class IsoTasksRelationManager extends RelationManager
{
    protected static string $relationship = 'isoTasks';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('weight')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(100)
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, $livewire) {
                        $this->updateRemainingWeight($state, $set, $livewire);
                    })
                    ->rules([
                        'required',
                        'numeric',
                        'min:1',
                        'max:100',
                        new IsoTaskWeightRule($this->getOwnerRecord()->id, $this->getStateId()),

                    ]),
                Forms\Components\DatePicker::make('end_date'),
                Forms\Components\Textarea::make('description')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\Placeholder::make('remaining_weight')
                    ->label('Remaining Weight')
                    ->content(function ($get) {
                        return $this->calculateRemainingWeight($get) . '%';
                    }),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('weight'),
                Tables\Columns\TextColumn::make('end_date')
                    ->date(),
                Tables\Columns\TextColumn::make('average_evaluation')
                    ->label('Average Evaluation')
                    ->getStateUsing(function (IsoTask $record) {
                        $evaluations = $record->evaluations;
                        if ($evaluations->isEmpty()) {
                            return 'No evaluations';
                        }
                        $average = $evaluations->avg('weight');
                        return number_format($average, 2);
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->before(function (array $data, CreateAction $action) {
                        $isoTasksCount = $this->getOwnerRecord()->isoTasks()->count();
                        if ($isoTasksCount >= 9) {
                            $action->cancel();
                            Notification::make()
                                ->title('Maximum ISO tasks reached')
                                ->body('This project already has the maximum number of ISO tasks (9).')
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected function checkMaxIsoTasks($fail)
    {
        $isoTasksCount = $this->getOwnerRecord()->isoTasks()->count();
        if ($isoTasksCount >= 9) {
            $fail("This project already has the maximum number of ISO tasks (9).");
        }
    }

    protected function updateRemainingWeight($state, callable $set, $livewire)
    {
        $currentWeight = (float) $this->getOwnerRecord()->isoTasks()
            ->when($this->getStateId(), fn ($query) => $query->where('id', $this->getStateId()))
            ->value('weight') ?? 0; // Get the current weight of the record being edited

        $totalWeight = (float) $this->getOwnerRecord()->isoTasks()
            ->when($this->getStateId(), fn ($query) => $query->where('id', '!=', $this->getStateId()))
            ->sum('weight');

        // Calculate remaining weight by subtracting the old weight and adding the new state
        $remainingWeight = 100 - $totalWeight + (float)$state - $currentWeight;
        $set('remaining_weight', max(0, $remainingWeight));
    }

    protected function validateTotalWeight($value, $fail)
    {
        // Get the current weight of the record being edited
        $currentWeight = (float) $this->getOwnerRecord()->isoTasks()
            ->when($this->getStateId(), fn ($query) => $query->where('id', $this->getStateId()))
            ->value('weight') ?? 0;

        // Calculate the total weight excluding the current record
        $totalWeight = (float) $this->getOwnerRecord()->isoTasks()
            ->when($this->getStateId(), fn ($query) => $query->where('id', '!=', $this->getStateId()))
            ->sum('weight');

        // Calculate the new total weight with the new value
        $newTotalWeight = $totalWeight + (float)$value - $currentWeight;

        // Check if the new total weight exceeds 100
        if ($newTotalWeight > 100) {
            $fail("The total weight of ISO tasks for this project cannot exceed 100%.");
        }
    }

    protected function calculateRemainingWeight($get)
    {
        $totalWeight = (float) $this->getOwnerRecord()->isoTasks()
            ->when($this->getStateId(), fn ($query) => $query->where('id', '!=', $this->getStateId()))
            ->sum('weight');
        $currentWeight = (float) ($get('weight') ?? 0);
        return max(0, 100 - $totalWeight - $currentWeight);
    }

    protected function getStateId()
    {
        return $this->getMountedActionFormModel()?->id;
    }

    protected function customWeightValidation($attribute, $value, $fail)
    {
        if ($value > 50) {
            $fail("The weight cannot exceed 50 for this project.");
        }
        $this->validateTotalWeight($value, $fail);
    }
}
