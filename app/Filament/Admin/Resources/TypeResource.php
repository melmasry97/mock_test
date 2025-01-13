<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TypeResource\Pages;
use App\Models\Type;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Admin\Resources\TypeCategoryResource;

class TypeResource extends Resource
{
    protected static ?string $model = Type::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Type Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Type Name'),
                        Forms\Components\Textarea::make('description')
                            ->maxLength(65535)
                            ->label('Description'),
                    ]),

                Forms\Components\Section::make('Type Categories')
                    ->schema([
                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('create_category')
                                ->label('Create New Category')
                                ->icon('heroicon-m-plus')
                                ->url(fn ($record) => TypeCategoryResource::getUrl('create', ['type_id' => $record->id]))
                                ->visible(fn ($record) => $record !== null),
                        ]),
                        Forms\Components\Placeholder::make('categories')
                            ->content(function ($record) {
                                if (!$record) return 'Save the type first to manage categories.';

                                return view('filament.components.type-categories-table', [
                                    'categories' => $record->categories()->with(['evaluations'])->get()->map(function ($category) {
                                        return [
                                            'id' => $category->id,
                                            'name' => $category->name,
                                            'description' => $category->description,
                                            'average_value' => $category->average_value ?? 'Not evaluated',
                                            'evaluation_count' => $category->evaluations->count(),
                                            'edit_url' => TypeCategoryResource::getUrl('edit', ['record' => $category])
                                        ];
                                    })
                                ]);
                            })
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Type Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('categories_count')
                    ->label('Categories')
                    ->counts('categories'),
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
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (Type $record) {
                        if ($record->categories()->count() > 0) {
                            throw new \Exception('Cannot delete type that has associated categories.');
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            foreach ($records as $record) {
                                if ($record->categories()->count() > 0) {
                                    throw new \Exception('Cannot delete types that have associated categories.');
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
            'index' => Pages\ListTypes::route('/'),
            'create' => Pages\CreateType::route('/create'),
            'edit' => Pages\EditType::route('/{record}/edit'),
        ];
    }
}
