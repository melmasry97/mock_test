<?php

namespace App\Filament\Resources\CategoryResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\CategoryElement;
use Filament\Forms\Components\Checkbox;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\RelationManagers\RelationManager;
use Illuminate\Support\Facades\Log;

class CategoryElementsRelationManager extends RelationManager
{
    protected static string $relationship = 'categoryElements';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('type')
                    ->options([
                        'text' => 'Text',
                        'number' => 'Number',
                        'percentage' => 'Percentage',
                        'boolean' => 'Boolean',
                    ])
                    ->required()
                    ->default('text')
                    ->reactive(),
                Forms\Components\Textarea::make('description')
                    ->maxLength(65535),
                Checkbox::make('negative')
                    ->label('Sign'),
                // Conditional rendering based on type
                Forms\Components\TextInput::make('value')
                    ->label('Value')
                    // ->default(function (RelationManager $livewire) {
                    //     $record = $livewire->getOwnerRecord()->categoryElements()->where('category_element_id', $livewire?->getRecord()->id)->first();
                    //     return $record ? $record->pivot->value : null;
                    // })
                    ->nullable()
                    ->visible(fn (callable $get) => in_array($get('type'), ['number', 'percentage', 'text']))
                    ->numeric(fn (callable $get) => in_array($get('type'), ['number', 'percentage'])),
                Checkbox::make('boolean_value')
                    ->label('Value')
                    // ->default(function (RelationManager $livewire) {
                    //     $record = $livewire->getOwnerRecord()->categoryElements()->where('category_element_id', $livewire?->getRecord()->id)->first();
                    //     return $record ? (bool)$record->pivot->value : false;
                    // })
                    ->visible(fn (callable $get) => $get('type') === 'boolean'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('type'),
                Tables\Columns\TextColumn::make('description')
                    ->limit(50),
                Tables\Columns\IconColumn::make('negative')
                    ->boolean()
                    ->label('Negative')
                    ->trueIcon('heroicon-o-x-circle')
                    ->falseIcon('heroicon-o-check-circle'),
                Tables\Columns\TextColumn::make('value')
                    ->label('value')
                    ->formatStateUsing(function ($state, $record) {
                        if ($state === null) return '-';
                        elseif ($record->type === 'boolean') {
                            return $state ? 'True' : 'False';
                        }
                        elseif ($record->type === 'percentage') {
                            return "{$state}%";
                        }elseif($record->type === 'text'){
                            return $state;
                        }else{
                            return $record->value ?? $state;
                        }
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
                Tables\Actions\AttachAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->form(function (Form $form, $record): Form {
                        return $form->schema([
                            Forms\Components\TextInput::make('name')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\Select::make('type')
                                ->options([
                                    'text' => 'Text',
                                    'number' => 'Number',
                                    'percentage' => 'Percentage',
                                    'boolean' => 'Boolean',
                                ])
                                ->required()
                                ->default('text')
                                ->reactive(),
                            Forms\Components\Textarea::make('description')
                                ->maxLength(65535),
                            Checkbox::make('negative')
                                ->label('Negativity'),
                            // Conditional rendering based on type
                            Forms\Components\TextInput::make('value')
                                ->label('Value')
                                ->default($record->pivot->value) // Ensure correct default value
                                ->nullable()
                                ->visible(fn (callable $get) => in_array($get('type'), ['number', 'percentage', 'text']))
                                ->numeric(fn (callable $get) => in_array($get('type'), ['number', 'percentage'])),
                            Checkbox::make('boolean_value')
                                ->label('Value')
                                ->default((bool)$record->pivot_value == '1' || $record->pivot_value == true)
                                ->visible(fn (callable $get) => $get('type') === 'boolean'),                            
                        ]);
                    })
                    ->mutateFormDataUsing(function (array $data): array {
                        // Convert boolean values to actual booleans
                        if ($data['type'] === 'boolean') {
                            $data['value'] = $data['boolean_value'] === '1' || $data['boolean_value'] === true;
                        } elseif ($data['type'] === 'number') {
                            $data['value'] = floatval($data['value']);
                        } else {
                            $data['value'] = $data['value'];
                        }

                        return $data;
                    })
                    ->using(function (Model $record, array $data): Model {
                        $record->update([
                            'name' => $data['name'],
                            'type' => $data['type'],
                            'description' => $data['description'],
                            'negative' => $data['negative'] ?? false,
                        ]);

                        $this->getOwnerRecord()->categoryElements()->updateExistingPivot($record->id, [
                            'value' => $data['value']
                        ]);

                        return $record;
                    }),
                Tables\Actions\DetachAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
