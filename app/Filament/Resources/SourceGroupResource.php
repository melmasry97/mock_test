<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SourceGroupResource\Pages;
use App\Models\SourceGroup;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SourceGroupResource extends Resource
{
    protected static ?string $model = SourceGroup::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Requirements';

    protected static ?string $navigationLabel = 'Requirement Source Groups';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Group Name'),

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
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Group Name'),

                Tables\Columns\TextColumn::make('description')
                    ->searchable()
                    ->limit(50)
                    ->label('Description'),

                Tables\Columns\TextColumn::make('sources_count')
                    ->counts('sources')
                    ->label('Sources'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (SourceGroup $record) {
                        // Check if there are any sources in this group
                        if ($record->sources()->count() > 0) {
                            throw new \Exception('Cannot delete group that contains sources.');
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            foreach ($records as $record) {
                                if ($record->sources()->count() > 0) {
                                    throw new \Exception('Cannot delete groups that contain sources.');
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
            'index' => Pages\ListSourceGroups::route('/'),
            'create' => Pages\CreateSourceGroup::route('/create'),
            'edit' => Pages\EditSourceGroup::route('/{record}/edit'),
        ];
    }
}
