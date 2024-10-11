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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('project_id')
                    ->label('Project')
                    ->options(Project::pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->reactive()
                    ->afterStateUpdated(fn (callable $set) => $set('weight', null))
                    ->rules(['required', function($attribute, $value, $fail) {
                        $count = IsoTask::where('project_id', $value)->count();
                        if ($count >= 9) {
                            $fail("This project already has the maximum number of ISO tasks (9).");
                        }
                    }]),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('weight')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(100)
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, $get) {
                        $projectId = $get('project_id');
                        if ($projectId) {
                            $totalWeight = IsoTask::where('project_id', $projectId)
                                ->where('id', '!=', $get('id'))
                                ->sum('weight');
                            $remainingWeight = 100 - $totalWeight - ($state ?? 0);
                            $set('remaining_weight', max(0, $remainingWeight));
                        }
                    })
                    ->rules(['required', 'numeric', 'min:1', 'max:100', function($attribute, $value, $fail) use ($form) {
                        $projectId = $form->getState()['project_id'];
                        $currentId = $form->getRecord()?->id;
                        $totalWeight = IsoTask::where('project_id', $projectId)
                            ->when($currentId, fn($query) => $query->where('id', '!=', $currentId))
                            ->sum('weight');
                        if (($totalWeight + $value) > 100) {
                            $fail("The total weight of ISO tasks for this project cannot exceed 100%.");
                        }
                    }]),
                Forms\Components\DatePicker::make('end_date'),
                Forms\Components\Textarea::make('description')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\Placeholder::make('remaining_weight')
                    ->label('Remaining Weight')
                    ->content(function ($get) {
                        $projectId = $get('project_id');
                        $currentId = $get('id');
                        $currentWeight = $get('weight') ?? 0;
                        if ($projectId) {
                            $totalWeight = IsoTask::where('project_id', $projectId)
                                ->when($currentId, fn($query) => $query->where('id', '!=', $currentId))
                                ->sum('weight');
                            return max(0, 100 - $totalWeight - $currentWeight) . '%';
                        }
                        return '100%';
                    }),
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
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make('viewEvaluations')
                    ->label('View Evaluations')
                    ->modalContent(function (IsoTask $record) {
                        return view('filament.resources.iso-task-resource.evaluations', ['isoTask' => $record]);
                    }),
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
