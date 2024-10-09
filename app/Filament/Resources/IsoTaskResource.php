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
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Support\Facades\DB;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Layout\Stack;
use Livewire\Component as Livewire;
use Illuminate\Validation\ValidationException;

class IsoTaskResource extends Resource
{
    protected static ?string $model = IsoTask::class;


    protected static ?string $navigationLabel = 'ISO';
    protected static ?string $navigationIcon = 'heroicon-o-document-text'; // Set a valid icon for the panel


    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.name') // Show category name
                    ->label('Category'), // Label for the field
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
                Tables\Actions\EditAction::make()
                    ->after(function (Livewire $livewire) {
                        $livewire->dispatch('iso-task-updated');
                    }),
                Tables\Actions\DeleteAction::make()
                    ->after(function (Livewire $livewire) {
                        $livewire->dispatch('iso-task-updated');
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->after(function (Livewire $livewire) {
                            $livewire->dispatch('iso-task-updated');
                        }),
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
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->maxLength(65535),
                Forms\Components\Select::make('category_id')
                    ->label('Category')
                    ->options(Category::pluck('name', 'id'))
                    ->required()
                    ->searchable(),
                Forms\Components\TextInput::make('weight')
                    ->label('Weight (%)')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->maxValue(100)
                    ->helperText(function () {
                        $totalWeight = IsoTask::sum('weight');
                        return "Current total weight: {$totalWeight}% / 100%";
                    }),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
        // Remove the when clause for now
    }
}
