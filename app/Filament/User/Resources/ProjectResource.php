<?php

namespace App\Filament\User\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Project;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\TypeCategory;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use App\Filament\User\Resources\ProjectResource\Pages\ViewProject;
use App\Filament\User\Resources\ProjectResource\Pages\ListProjects;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Projects';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->disabled()
                    ->maxLength(255),

                Forms\Components\Textarea::make('description')
                    ->disabled()
                    ->maxLength(65535)
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('weight')
                    ->disabled()
                    ->numeric(),

                Forms\Components\Section::make('Categories To Evaluate')
                    ->schema([
                        Forms\Components\Placeholder::make('categories')
                            ->content(function ($record) {
                                return view('filament.components.type-categories-table', [
                                    'categories' => TypeCategory::whereHas('tasks', function ($query) use ($record) {
                                        $query->where('project_id', $record->id);
                                    })
                                    ->whereDoesntHave('evaluations', function ($query) {
                                        $query->where('user_id', Auth::id());
                                    })
                                    ->get()
                                    ->map(function ($category) {
                                        return [
                                            'id' => $category->id,
                                            'name' => $category->name,
                                            'description' => $category->description,
                                            'average_value' => $category->average_value ?? 'Not evaluated',
                                            'evaluation_count' => $category->evaluations->count(),
                                            'edit_url' => ''  // No edit URL needed for user view
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
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->searchable(),

                Tables\Columns\TextColumn::make('weight')
                    ->numeric(2)
                    ->sortable(),

                Tables\Columns\TextColumn::make('modules_count')
                    ->counts('modules')
                    ->label('Modules'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProjects::route('/'),
            'view' => ViewProject::route('/{record}'),
        ];
    }
}
