<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SourceResource\Pages;
use App\Models\Source;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SourceResource extends Resource
{
    protected static ?string $model = Source::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';

    protected static ?string $navigationGroup = 'Source Management';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('id')
                    ->relationship('sourceGroup', 'name')
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
                    ->label('Source Group'),

                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Source Name'),

                Forms\Components\Textarea::make('description')
                    ->maxLength(65535)
                    ->label('Description')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sourceGroup.name')
                    ->searchable()
                    ->sortable()
                    ->label('Group'),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Source Name'),

                Tables\Columns\TextColumn::make('description')
                    ->searchable()
                    ->limit(50)
                    ->label('Description'),

                Tables\Columns\TextColumn::make('tasks_count')
                    ->counts('tasks')
                    ->label('Tasks'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('id')
                    ->relationship('sourceGroup', 'name')
                    ->label('Source Group')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (Source $record) {
                        if ($record->tasks()->count() > 0) {
                            throw new \Exception('Cannot delete source that has associated tasks.');
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            foreach ($records as $record) {
                                if ($record->tasks()->count() > 0) {
                                    throw new \Exception('Cannot delete sources that have associated tasks.');
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
            'index' => Pages\ListSources::route('/'),
            'create' => Pages\CreateSource::route('/create'),
            'edit' => Pages\EditSource::route('/{record}/edit'),
        ];
    }
}
