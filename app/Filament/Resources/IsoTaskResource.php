<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IsoTaskResource\Pages;
use App\Models\IsoTask;
use App\Models\Category; // Import Category model
use App\Enums\TaskState;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class IsoTaskResource extends Resource
{
    protected static ?string $model = IsoTask::class;


    protected static ?string $navigationLabel = 'ISO';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack'; // Set a valid icon for the panel


    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.name') // Show category name
                    ->label('Category'), // Label for the field
                Tables\Columns\TextColumn::make('state')
                    ->sortable(),
                Tables\Columns\TextColumn::make('weight') // Add weight column
                    ->label('Weight (%)') // Label for the field
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Define your filters here
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
            'index' => Pages\ListIsoTasks::route('/'),
            'create' => Pages\CreateIsoTask::route('/create'),
            'edit' => Pages\EditIsoTask::route('/{record}/edit'),
        ];
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->required()
                    ->maxLength(65535),
                Forms\Components\Select::make('category_id') // Change 'category' to 'category_id'
                    ->label('Category') // Label for the field
                    ->options(Category::where('name', 'iso')->pluck('name', 'id')) // Fetch categories with name 'Qa'
                    ->required(),
                Forms\Components\Select::make('state')
                    ->options(TaskState::class)
                    ->required(),
                Forms\Components\TextInput::make('weight')
                    ->label('Weight (%)') // Label for the field
                    ->required()
                    ->numeric() // Ensure it's a numeric input
                    ->default(0) // Set a default value if needed
                    ->minValue(0) // Ensure it's within the valid range
                    ->maxValue(100), // Ensure it's within the valid range
            ]);
    }
}
