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

    protected static ?string $navigationLabel = 'ISO25010 QAs';
    protected static ?string $navigationGroup = 'knowledge';
    protected static ?int $navigationSort = 9;
    protected static ?int $navigationGroupSort = 9999;

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
                    ->nullable()
                    ->disabled(),
                Forms\Components\TextInput::make('weight')
                    ->nullable()
                    ->numeric()
                    ->disabled(),
                Forms\Components\DatePicker::make('end_date')
                    ->nullable()
                    ->disabled(),
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
                Tables\Actions\Action::make('evaluate')
                    ->label('Evaluate')
                    ->icon('heroicon-o-star')
                    ->modalHeading('Evaluate ISO Task')
                    ->modalSubmitActionLabel('Save Evaluation')
                    ->modalIcon('heroicon-o-star')
                    ->form([
                        Forms\Components\TextInput::make('weight')
                            ->required()
                            ->numeric()
                            ->step(0.1)
                            ->rules(['numeric', 'min:-100', 'max:100']),
                    ])
                    ->action(function (IsoTask $record, array $data): void {
                        $userEvaluation = ($record->weight / 100) * $data['weight'];
                        $record->evaluations()->updateOrCreate(
                            ['user_id' => Auth::id()],
                            ['weight' => $userEvaluation]
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
