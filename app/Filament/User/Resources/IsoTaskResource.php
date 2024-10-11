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

    protected static ?string $navigationLabel = 'ISO QAs';

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
                    ->step(0.1)
                    ->rules(['numeric', 'min:-100', 'max:100']) // Validation rules for min and max
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
                        return number_format($record->evaluations->sum('weight'), 3); // Format to 3 decimal places
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
                        Forms\Components\TextInput::make('weight') // Changed from Select to TextInput
                            ->required()
                            ->numeric() // Ensure it accepts numeric values
                            ->step(0.1) // Allow decimal input
                            ->rules(['numeric', 'min:-100', 'max:100']), // Validation rules for min and max
                    ])
                    ->action(function (IsoTask $record, array $data): void {
                        // Calculate the user's evaluation based on the provided formula
                        $userEvaluation = ($record->weight / 100) * $data['weight'];

                        $record->evaluations()->updateOrCreate(
                            ['user_id' => Auth::id()],
                            ['weight' => $userEvaluation] // Use the calculated evaluation
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
