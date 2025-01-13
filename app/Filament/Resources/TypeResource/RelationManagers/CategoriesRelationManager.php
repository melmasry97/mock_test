<?php

namespace App\Filament\Resources\TypeResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class CategoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'categories';

    protected static ?string $title = 'Type Categories';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Category Name'),

                Forms\Components\Textarea::make('description')
                    ->maxLength(65535)
                    ->label('Description')
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('time_period')
                    ->required()
                    ->numeric()
                    ->label('Evaluation Time Period (days)'),

                Forms\Components\TextInput::make('value')
                    ->numeric()
                    ->default(0)
                    ->step(0.01)
                    ->label('Evaluation Value'),

                Forms\Components\TextInput::make('average_value')
                    ->numeric()
                    ->default(0)
                    ->step(0.01)
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText('This value is automatically calculated from user evaluations')
                    ->label('Evaluation Average Value'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Category Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->searchable(),

                Tables\Columns\TextColumn::make('time_period')
                    ->numeric()
                    ->sortable()
                    ->label('Time Period (days)'),

                Tables\Columns\TextColumn::make('value')
                    ->numeric(2)
                    ->sortable(),

                Tables\Columns\TextColumn::make('average_value')
                    ->numeric(2)
                    ->sortable()
                    ->label('Average Value'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function ($record) {
                        if ($record->tasks()->count() > 0) {
                            throw new \Exception('Cannot delete category that has associated tasks.');
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            foreach ($records as $record) {
                                if ($record->tasks()->count() > 0) {
                                    throw new \Exception('Cannot delete categories that have associated tasks.');
                                }
                            }
                        }),
                ]),
            ]);
    }
}
