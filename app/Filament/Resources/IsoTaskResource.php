<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IsoTaskResource\Pages;
use App\Models\IsoTask;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class IsoTaskResource extends Resource
{
    protected static ?string $model = IsoTask::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'ISO25010 QAs';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\Select::make('project_id')
                    ->relationship('project', 'name')
                    ->nullable()
                    ->searchable(),
                Forms\Components\TextInput::make('weight')
                    ->nullable()
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(100),
                Forms\Components\DatePicker::make('end_date')
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
}
