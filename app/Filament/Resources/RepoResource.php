<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\Task;
use Filament\Tables;
use App\Enums\TaskState;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\ProjectModule;
use App\Models\Metric;
use App\Models\IsoTask;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\RepoResource\Pages;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class RepoResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Approved Tasks';

    protected static ?string $navigationGroup = 'Requirement Tasks';

    protected static ?int $navigationSort = 3;

    protected static ?string $slug = 'repos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->maxLength(65535),
                Forms\Components\Hidden::make('state')
                    ->default(TaskState::REPO),
                Forms\Components\Select::make('project_module_id')
                    ->label('Project Module')
                    ->options(ProjectModule::all()->pluck('name', 'id'))
                    ->searchable()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Task ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Task Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('projectModule.project.name')
                    ->label('Project')
                    ->sortable(),

                Tables\Columns\TextColumn::make('projectModule.name')
                    ->label('Module')
                    ->sortable(),


                Tables\Columns\TextColumn::make('sourceGroup.name')
                    ->label('Req Source Group')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('source.name')
                    ->label('Req Source')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('type.name')
                    ->label('ReqType')
                    ->sortable()
                    ->searchable(),

                    Tables\Columns\TextColumn::make('projectModule.weight')
                    ->label('Average Team Weight')
                    ->numeric(2)
                    ->sortable(),

                    Tables\Columns\TextColumn::make('type.typeCategory.weight')
                    ->label('Type Category Weight')
                    ->numeric(2)
                    ->sortable(),

                Tables\Columns\TextColumn::make('rice_score')
                    ->label('RICE Score')
                    ->numeric(2)
                    ->sortable(),

                Tables\Columns\TextColumn::make('overall_evaluation_value')
                    ->label('Average Team Weight')
                    ->numeric(2)
                    ->sortable(),

                Tables\Columns\TextColumn::make('weight')
                    ->label('Final Weight')
                    ->numeric(2)
                    ->sortable(),

                Tables\Columns\TextColumn::make('user_evaluation_remaining_time')
                    ->label('Evaluation Time')
                    ->badge()
                    ->color(fn ($state) => $state === 'Ended' ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color('warning'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('project')
                    ->relationship('projectModule.project', 'name'),
                Tables\Filters\SelectFilter::make('module')
                    ->relationship('projectModule', 'name'),
                Tables\Filters\SelectFilter::make('source_group')
                    ->relationship('sourceGroup', 'name')
                    ->label('Source Group')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('source')
                    ->relationship('source', 'name')
                    ->label('Source')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('type')
                    ->relationship('type', 'name')
                    ->label('Type')
                    ->searchable()
                    ->preload(),
            ])
            ->defaultSort('id', 'desc')
            ->actions([
                Tables\Actions\Action::make('force_evaluation_end')
                    ->label('End User Evaluation')
                    ->icon('heroicon-o-clock')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalDescription('Are you sure you want to end the user evaluation period? This will calculate the final weight.')
                    ->action(function (Task $record) {
                        $record->update([
                            'evaluation_end_time' => now()
                        ]);
                        $record->calculateOverallEvaluation();
                        $record->calculateFinalWeight();
                        Notification::make()
                            ->success()
                            ->title('User evaluation period has been ended and final weight calculated')
                            ->send();
                    })
                    ->visible(fn (Task $record) =>
                        $record->evaluation_end_time &&
                        $record->evaluation_end_time->isFuture()
                    ),
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
            'index' => Pages\ListRepos::route('/'),
            'create' => Pages\CreateRepo::route('/create'),
            'edit' => Pages\EditRepo::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('status', 'approved');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }
}
