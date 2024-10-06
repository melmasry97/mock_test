<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\CategoryElement;
use Filament\Resources\Resource;
use App\Filament\Resources\CategoryElementResource\Pages;
use Filament\Forms\Components\Checkbox;

class CategoryElementResource extends Resource
{
    protected static ?string $model = CategoryElement::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('categories')
                    ->multiple()
                    ->relationship('categories', 'name')
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                    ]),
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
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Checkbox::make('negative')
                    ->label('Negative')
                    ->visible(fn (callable $get) => (bool) $get('negative')), // Always visible when type is boolean
                Forms\Components\TextInput::make('value')
                    ->label('Value')
                    ->numeric()
                    ->visible(fn (callable $get) => in_array($get('type'), ['number', 'percentage', 'boolean'])), // Now also visible for boolean
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Type'),
                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\IconColumn::make('negative')
                    ->boolean()
                    ->label('Negative')
                    ->trueIcon('heroicon-o-x-circle')
                    ->falseIcon('heroicon-o-check-circle'),
                Tables\Columns\TextColumn::make('value')
                    ->label('Value')
                    ->formatStateUsing(fn ($state, $record) => $record->type === 'percentage' ? "{$state}%" : $state)
                    // ->visible(fn ($record): bool => in_array($record->type, ['number', 'percentage', 'text'])),
            ])
            ->filters([
                //
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategoryElements::route('/'),
            'create' => Pages\CreateCategoryElement::route('/create'),
            'edit' => Pages\EditCategoryElement::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return true;
    }

    public static function canCreate(): bool
    {
        return true;
    }
}
