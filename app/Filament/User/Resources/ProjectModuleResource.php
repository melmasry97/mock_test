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

class ProjectModuleResource extends Resource
{
    protected static ?string $model = ProjectModule::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = 'Projects';

    protected static ?string $navigationLabel = 'Project Modules';

    protected static ?int $navigationSort = 2;

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
                    ->required(),
                Forms\Components\DatePicker::make('end_date')
                    ->label('End Date')
                    ->required(),
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
                Tables\Columns\TextColumn::make('your_evaluation')
                    ->label('Your Evaluation')
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
                Tables\Actions\EditAction::make(),
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
                    ->visible(fn (ProjectModule $record) => !$record->evaluations()->where('user_id', Auth::id())->exists()),
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
            'index' => Pages\ListProjectModules::route('/'),
            'create' => Pages\CreateProjectModule::route('/create'),
            'edit' => Pages\EditProjectModule::route('/{record}/edit'),
        ];
    }
}
