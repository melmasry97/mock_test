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
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ProjectModuleResource extends Resource
{
    protected static ?string $model = ProjectModule::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Projects';

    protected static ?string $navigationLabel = 'Project Modules';

    protected static ?int $navigationSort = 3;

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
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->description(fn (ProjectModule $record) => $record->description),

                Tables\Columns\TextColumn::make('project.name')
                    ->searchable()
                    ->sortable()
                    ->label('Project'),

                Tables\Columns\TextColumn::make('weight')
                    ->numeric(2)
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_date')
                    ->date()
                    ->sortable()
                    ->formatStateUsing(function ($state) {
                        if (!$state) return 'No deadline';
                        $endTime = \Carbon\Carbon::parse($state);
                        return now()->isAfter($endTime) ? 'Ended' : $endTime->diffForHumans();
                    })
                    ->badge()
                    ->color(fn ($state) =>
                        !$state ? 'gray' :
                        (now()->isAfter(\Carbon\Carbon::parse($state)) ? 'danger' : 'warning')
                    ),

                Tables\Columns\TextColumn::make('evaluations_count')
                    ->counts('evaluations')
                    ->label('Total Evaluations')
                    ->sortable(),

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
                Tables\Filters\SelectFilter::make('project')
                    ->relationship('project', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('has_ended')
                    ->label('Evaluation Status')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->options([
                                'ended' => 'Ended',
                                'active' => 'Active',
                                'no_deadline' => 'No Deadline',
                            ])
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['status'] ?? null) {
                            'ended' => $query->whereNotNull('end_date')->where('end_date', '<', now()),
                            'active' => $query->where(function ($query) {
                                $query->whereNull('end_date')
                                    ->orWhere('end_date', '>', now());
                            }),
                            'no_deadline' => $query->whereNull('end_date'),
                            default => $query
                        };
                    })
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('viewEvaluations')
                    ->label('View Evaluations')
                    ->icon('heroicon-o-eye')
                    ->modalContent(function (ProjectModule $record) {
                        return view('filament.resources.project-module-resource.evaluations', ['projectModule' => $record]);
                    }),
                Tables\Actions\Action::make('end_evaluation')
                    ->label('End Evaluation')
                    ->icon('heroicon-o-flag')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalDescription('Are you sure you want to end the evaluation period for this module? This action cannot be undone.')
                    ->action(function (ProjectModule $record) {
                        if ($record->end_date && now()->isBefore($record->end_date)) {
                            $record->update([
                                'end_date' => now()
                            ]);
                            Notification::make()
                                ->success()
                                ->title('Module evaluation period has been ended')
                                ->send();
                        }
                    })
                    ->visible(fn (ProjectModule $record) =>
                        !$record->end_date ||
                        \Carbon\Carbon::parse($record->end_date)->isFuture()
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('end_evaluations')
                        ->label('End Evaluations')
                        ->icon('heroicon-o-flag')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalDescription('Are you sure you want to end the evaluation period for all selected modules? This action cannot be undone.')
                        ->action(function (Collection $records) {
                            $records->each(function ($record) {
                                if (!$record->end_date || now()->isBefore($record->end_date)) {
                                    $record->update(['end_date' => now()]);
                                }
                            });
                            Notification::make()
                                ->success()
                                ->title('Evaluation periods have been ended')
                                ->send();
                        })
                ]),
            ])
            ->defaultSort('name', 'asc')
            ->searchable();
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
