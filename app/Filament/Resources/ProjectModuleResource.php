<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectModuleResource\Pages;
use App\Models\ProjectModule;
use App\Models\Project;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ProjectModuleResource extends Resource
{
    protected static ?string $model = ProjectModule::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('weight')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100),
                Forms\Components\Select::make('project_id')
                    ->label('Project')
                    ->options(Project::pluck('name', 'id'))
                    ->required()
                    ->searchable(),
                Forms\Components\DatePicker::make('end_date'),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('weight'),
                Tables\Columns\TextColumn::make('project.name')
                    ->label('Project'),
                Tables\Columns\TextColumn::make('end_date')
                    ->date(),
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
                Action::make('viewEvaluations')
                    ->label('View Evaluations')
                    ->icon('heroicon-o-eye')
                    ->modalContent(function (ProjectModule $record) {
                        return view('filament.resources.project-module-resource.evaluations', ['projectModule' => $record]);
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
            'index' => Pages\ListProjectModules::route('/'),
            'create' => Pages\CreateProjectModule::route('/create'),
            'edit' => Pages\EditProjectModule::route('/{record}/edit'),
        ];
    }
}
