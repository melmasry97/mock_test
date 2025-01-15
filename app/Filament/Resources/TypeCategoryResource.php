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

    protected static ?string $navigationGroup = 'Requirements';

    protected static ?string $navigationLabel = 'Requirement Type Categories';

    protected static ?int $navigationSort = 4;

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('type_id')
                    ->relationship('type', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->maxLength(65535),
                    ])
                    ->label('Type'),

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

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type.name')
                    ->sortable()
                    ->searchable()
                    ->label('Type'),

                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable()
                    ->label('Category Name'),

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
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->relationship('type', 'name')
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
