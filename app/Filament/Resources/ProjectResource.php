<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Models\Project;
use App\Models\Type;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Textarea::make('description')
                    ->maxLength(65535)
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('weight')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText('This value is automatically calculated'),

                Forms\Components\DateTimePicker::make('evaluation_end_time')
                    ->label('Evaluation End Time')
                    ->helperText('After this time, users cannot evaluate categories')
                    ->visible(fn ($livewire) => $livewire instanceof Pages\CreateProject || $livewire instanceof Pages\EditProject)
                    ->timezone('Asia/Kuwait'),

                Forms\Components\Actions::make([
                    Forms\Components\Actions\Action::make('attach_type')
                        ->label('Attach Type')
                        ->icon('heroicon-m-plus')
                        ->modalHeading('Attach Type to Project')
                        ->form([
                            Forms\Components\Select::make('type_id')
                                ->label('Type')
                                ->options(function (Project $record) {
                                    return Type::whereDoesntHave('projects', function ($query) use ($record) {
                                        $query->where('projects.id', $record->id);
                                    })->pluck('name', 'id');
                                })
                                ->required()
                                ->searchable(),
                        ])
                        ->action(function (array $data, $record): void {
                            $record->types()->attach($data['type_id']);

                            Notification::make()
                                ->success()
                                ->title('Type attached successfully')
                                ->send();
                        })
                        ->visible(fn ($record) => $record !== null),
                ])->columnSpanFull(),

                Forms\Components\Section::make('Linked Types')
                    ->schema([
                        Forms\Components\View::make('filament.resources.project.types-table')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->searchable(),

                Tables\Columns\TextColumn::make('weight')
                    ->numeric(2)
                    ->sortable(),

                Tables\Columns\TextColumn::make('evaluation_end_time')
                    ->label('Evaluation Ends')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->formatStateUsing(function ($state) {
                        if (!$state) return 'No deadline';

                        $endTime = \Carbon\Carbon::parse($state);
                        if (now()->isAfter($endTime)) {
                            return 'Evaluation ended';
                        }

                        return $endTime->diffForHumans([
                            'parts' => 2,
                            'join' => true,
                        ]);
                    }),

                Tables\Columns\TextColumn::make('types_count')
                    ->counts('types')
                    ->label('Types'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
            'evaluate-categories' => Pages\EvaluateCategories::route('/{record}/evaluate-categories/{typeId}'),
        ];
    }
}
