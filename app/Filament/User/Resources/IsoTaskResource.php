<?php

namespace App\Filament\User\Resources;

use App\Filament\User\Resources\IsoTaskResource\Pages;
use App\Models\IsoTask;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class IsoTaskResource extends Resource
{
    protected static ?string $model = IsoTask::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->disabled(),
                Forms\Components\TextInput::make('weight')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
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
                Tables\Columns\TextColumn::make('weight')
                    ->sortable(),
                Tables\Columns\TextColumn::make('project.name')
                    ->label('Project')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('your_evaluation')
                    ->label('Your Evaluation')
                    ->getStateUsing(function (IsoTask $record) {
                        $evaluation = $record->evaluations()->where('user_id', Auth::id())->first();
                        return $evaluation ? $evaluation->weight : 'Not evaluated';
                    }),
                Tables\Columns\TextColumn::make('average_evaluation')
                    ->label('Average Evaluation')
                    ->getStateUsing(function (IsoTask $record) {
                        $evaluations = $record->evaluations;
                        if ($evaluations->isEmpty()) {
                            return 'No evaluations';
                        }
                        $average = $evaluations->avg('weight');
                        return number_format($average, 2);
                    }),
                Tables\Columns\TextColumn::make('total_evaluation')
                    ->label('Total Evaluation')
                    ->getStateUsing(function (IsoTask $record) {
                        return $record->evaluations->sum('weight');
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('evaluate')
                    ->label('Evaluate')
                    ->icon('heroicon-o-star')
                    ->modalHeading('Evaluate ISO Task')
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
                    ->action(function (IsoTask $record, array $data): void {
                        $record->evaluations()->updateOrCreate(
                            ['user_id' => Auth::id()],
                            ['weight' => $data['weight']]
                        );
                    })
                    ->visible(function (IsoTask $record): bool {
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
            ->with(['evaluations', 'project']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIsoTasks::route('/'),
        ];
    }
}
