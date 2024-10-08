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
                    ->afterStateUpdated(function (Get $get, Set $set) {
                        $currentId = $get('id');
                        $currentWeight = $get('weight');

                        $totalWeight = IsoTask::when($currentId, function ($query) use ($currentId) {
                                $query->where('id', '!=', $currentId);
                            })
                            ->sum('weight') + $currentWeight;

                        if ($totalWeight > 100) {
                            $set('weight', 100 - (IsoTask::when($currentId, function ($query) use ($currentId) {
                                $query->where('id', '!=', $currentId);
                            })->sum('weight')));
                        }
                    })
                    ->helperText(function (Get $get) {
                        $currentId = $get('id');
                        $currentWeight = $get('weight');

                        $totalWeight = IsoTask::when($currentId, function ($query) use ($currentId) {
                                $query->where('id', '!=', $currentId);
                            })
                            ->sum('weight') + $currentWeight;

                        return "Total weight: {$totalWeight}% / 100%";
                    }),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
        // Remove the when clause for now
    }
}
