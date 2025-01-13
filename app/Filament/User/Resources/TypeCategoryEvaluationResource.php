<?php

namespace App\Filament\User\Resources;

use App\Filament\User\Resources\TypeCategoryEvaluationResource\Pages;
use App\Models\TypeCategory;
use App\Models\TypeCategoryEvaluation;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TypeCategoryEvaluationResource extends Resource
{
    protected static ?string $model = TypeCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationLabel = 'Evaluate Categories';

    protected static ?int $navigationSort = 1;

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('fibonacci_weight')
                    ->options([
                        1 => '1 - Very Low',
                        2 => '2 - Low',
                        3 => '3 - Medium',
                        5 => '5 - High',
                        8 => '8 - Very High',
                    ])
                    ->required()
                    ->label('Evaluation Score'),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type.name')
                    ->searchable()
                    ->sortable()
                    ->label('Type'),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Category Name'),

                Tables\Columns\TextColumn::make('description')
                    ->searchable()
                    ->limit(50)
                    ->label('Description'),

                Tables\Columns\TextColumn::make('time_period')
                    ->numeric()
                    ->sortable()
                    ->label('Time Period (days)'),

                Tables\Columns\TextColumn::make('remaining_time')
                    ->label('Time Remaining'),

                Tables\Columns\TextColumn::make('average_value')
                    ->numeric(2)
                    ->sortable()
                    ->label('Current Average'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->relationship('type', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Type'),
            ])
            ->actions([
                Tables\Actions\Action::make('evaluate')
                    ->form([
                        Forms\Components\Select::make('fibonacci_weight')
                            ->options([
                                1 => '1 - Very Low',
                                2 => '2 - Low',
                                3 => '3 - Medium',
                                5 => '5 - High',
                                8 => '8 - Very High',
                            ])
                            ->required()
                            ->label('Evaluation Score'),
                    ])
                    ->action(function (TypeCategory $record, array $data): void {
                        if (!$record->canBeEvaluated()) {
                            throw new \Exception('This category cannot be evaluated anymore.');
                        }

                        TypeCategoryEvaluation::create([
                            'type_category_id' => $record->category_id,
                            'user_id' => Auth::id(),
                            'fibonacci_weight' => $data['fibonacci_weight'],
                        ]);

                        $record->calculateAverageEvaluation();
                    })
                    ->visible(fn (TypeCategory $record): bool => $record->canBeEvaluated())
                    ->modalHeading('Evaluate Category')
                    ->modalDescription('Choose a Fibonacci number to evaluate this category.')
                    ->modalSubmitActionLabel('Submit Evaluation'),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->whereDoesntHave('evaluations', function ($query) {
                    $query->where('user_id', Auth::id());
                })
                ->where('created_at', '>', now()->subDays(TypeCategory::max('time_period')))
            );
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTypeCategories::route('/'),
        ];
    }
}
