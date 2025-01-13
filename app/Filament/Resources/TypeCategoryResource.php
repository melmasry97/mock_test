<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TypeCategoryResource\Pages;
use App\Models\TypeCategory;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;

class TypeCategoryResource extends Resource
{
    protected static ?string $model = TypeCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationGroup = 'Type Management';

    protected static ?int $navigationSort = 2;

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('type_id')
                    ->relationship('type', 'type_name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('type_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('type_description')
                            ->maxLength(65535),
                    ])
                    ->label('Type'),

                Forms\Components\TextInput::make('category_name')
                    ->required()
                    ->maxLength(255)
                    ->label('Category Name'),

                Forms\Components\Textarea::make('category_description')
                    ->maxLength(65535)
                    ->label('Description')
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('evaluation_time_period')
                    ->required()
                    ->numeric()
                    ->label('Evaluation Time Period (days)'),

                Forms\Components\TextInput::make('evaluation_value')
                    ->numeric()
                    ->default(0)
                    ->step(0.01)
                    ->label('Evaluation Value'),

                Forms\Components\TextInput::make('evaluation_average_value')
                    ->numeric()
                    ->default(0)
                    ->step(0.01)
                    ->label('Evaluation Average Value'),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type.type_name')
                    ->searchable()
                    ->sortable()
                    ->label('Type'),

                Tables\Columns\TextColumn::make('category_name')
                    ->searchable()
                    ->sortable()
                    ->label('Category Name'),

                Tables\Columns\TextColumn::make('category_description')
                    ->searchable()
                    ->limit(50)
                    ->label('Description'),

                Tables\Columns\TextColumn::make('evaluation_time_period')
                    ->numeric()
                    ->sortable()
                    ->label('Time Period (days)'),

                Tables\Columns\TextColumn::make('evaluation_value')
                    ->numeric(2)
                    ->sortable()
                    ->label('Value'),

                Tables\Columns\TextColumn::make('evaluation_average_value')
                    ->numeric(2)
                    ->sortable()
                    ->label('Avg Value'),

                Tables\Columns\TextColumn::make('tasks_count')
                    ->counts('tasks')
                    ->label('Tasks'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->relationship('type', 'type_name')
                    ->searchable()
                    ->preload()
                    ->label('Type'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (TypeCategory $record) {
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTypeCategories::route('/'),
            'create' => Pages\CreateTypeCategory::route('/create'),
            'edit' => Pages\EditTypeCategory::route('/{record}/edit'),
        ];
    }
}
