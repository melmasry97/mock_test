<?php

namespace App\Filament\User\Resources;

use App\Filament\User\Resources\ProjectResource\Pages;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use App\Models\TypeCategory;
use App\Models\ProjectTypeCategoryEvaluation;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->disabled(),

                Forms\Components\Textarea::make('description')
                    ->maxLength(65535)
                    ->columnSpanFull()
                    ->disabled(),

                Forms\Components\Section::make('Linked Types')
                    ->schema([
                        Forms\Components\Placeholder::make('types')
                            ->content(function ($record) {
                                $types = $record->types()
                                    ->with(['categories' => function ($query) {
                                        $query->with(['evaluations' => function ($query) {
                                            $query->where('user_id', Auth::id());
                                        }]);
                                    }])
                                    ->get();

                                return view('filament.resources.project.types-table', [
                                    'record' => $record,
                                    'types' => $types
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

                Tables\Columns\TextColumn::make('types_count')
                    ->counts('types')
                    ->label('Types')
                    ->badge(),

                Tables\Columns\TextColumn::make('evaluation_end_time')
                    ->label('Evaluation Deadline')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->formatStateUsing(function ($state) {
                        if (!$state) return 'No deadline';

                        $endTime = \Carbon\Carbon::parse($state);
                        if (now()->isAfter($endTime)) {
                            return 'Evaluation ended';
                        }

                        return $endTime->diffForHumans([
                            'parts' => 2,
                            'join' => true,
                        ]);
                    })
                    ->badge()
                    ->color(fn ($state) =>
                        !$state ? 'gray' :
                        (now()->isAfter(\Carbon\Carbon::parse($state)) ? 'danger' : 'warning')
                    ),

                Tables\Columns\TextColumn::make('categories_count')
                    ->label('Categories')
                    ->badge()
                    ->color('success')
                    ->getStateUsing(function (Project $record) {
                        return $record->types()->whereHas('categories')->count();
                    }),

                Tables\Columns\TextColumn::make('evaluated_categories')
                    ->label('Evaluated')
                    ->badge()
                    ->color('success')
                    ->getStateUsing(function (Project $record) {
                        return ProjectTypeCategoryEvaluation::where('project_id', $record->id)
                            ->where('user_id', Auth::id())
                            ->distinct('type_id')
                            ->count('type_id');
                    }),

                Tables\Columns\TextColumn::make('remaining_categories')
                    ->label('Remaining')
                    ->badge()
                    ->color('warning')
                    ->getStateUsing(function (Project $record) {
                        $totalTypes = $record->types()->whereHas('categories')->count();
                        $evaluatedTypes = ProjectTypeCategoryEvaluation::where('project_id', $record->id)
                            ->where('user_id', Auth::id())
                            ->distinct('type_id')
                            ->count('type_id');
                        return $totalTypes - $evaluatedTypes;
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
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
            'index' => Pages\ListProjects::route('/'),
            'view' => Pages\ViewProject::route('/{record}'),
            'evaluate-categories' => Pages\EvaluateCategories::route('/{record}/evaluate-categories/{typeId}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereHas('typeCategories', function ($query) {
            $query->whereDoesntHave('evaluations', function ($q) {
                $q->where('user_id', Auth::id());
            });
        })->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getNavigationBadge() ? 'warning' : null;
    }
}
