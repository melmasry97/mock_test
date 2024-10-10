<?php

namespace App\Filament\User\Resources;

use App\Filament\User\Resources\ProjectModuleResource\Pages;
use App\Models\ProjectModule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ProjectModuleResource extends Resource
{
    protected static ?string $model = ProjectModule::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

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
                Forms\Components\Select::make('project_id')
                    ->relationship('project', 'name')
                    ->required()
                    ->disabled(),
                Forms\Components\DatePicker::make('end_date')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('project.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('evaluations.weight')
                    ->label('Your Evaluation')
                    ->default('Not evaluated')
                    ->getStateUsing(function (ProjectModule $record) {
                        $evaluation = $record->evaluations()->where('user_id', Auth::id())->first();
                        return $evaluation ? $evaluation->weight : 'Not evaluated';
                    }),
                Tables\Columns\TextColumn::make('average_evaluation')
                    ->label('Average Evaluation')
                    ->getStateUsing(function (ProjectModule $record) {
                        $evaluations = $record->evaluations;
                        if ($evaluations->isEmpty()) {
                            return 'No evaluations';
                        }
                        $average = $evaluations->avg('weight');
                        return number_format($average, 2);
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('evaluate')
                    ->label('Evaluate')
                    ->icon('heroicon-o-star')
                    ->modalHeading('Evaluate Project Module')
                    ->modalSubmitActionLabel('Save Evaluation')
                    ->modalIcon('heroicon-o-star')
                    ->form([
                        Forms\Components\Select::make('weight')
                            ->options([
                                0 => '0',
                                1 => '1',
                                2 => '2',
                                3 => '3',
                                5 => '5',
                            ])
                            ->required(),
                    ])
                    ->action(function (ProjectModule $record, array $data): void {
                        $record->evaluations()->updateOrCreate(
                            ['user_id' => Auth::id()],
                            ['weight' => $data['weight']]
                        );
                    })
                    ->visible(function (ProjectModule $record): bool {
                        $endDate = Carbon::parse($record->end_date);
                        $today = Carbon::today();
                        $userHasEvaluated = $record->evaluations()->where('user_id', Auth::id())->exists();

                        return $today->lte($endDate) && !$userHasEvaluated;
                    }),
            ])
            ->bulkActions([]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount(['evaluations' => function ($query) {
                $query->where('user_id', Auth::id());
            }])
            ->with('evaluations');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjectModules::route('/'),
        ];
    }
}
