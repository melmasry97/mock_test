                Forms\Components\Section::make('RICE Weight')
                    ->schema([
                        Forms\Components\Grid::make(4)
                            ->schema([
                                Forms\Components\Select::make('reach')
                                    ->label('Reach (R)')
                                    ->options([
                                        1 => '1 - Minimal: Affects very few users (<5%)',
                                        3 => '3 - Low: Affects some users (5-20%)',
                                        4 => '4 - Medium: Affects a good amount of users (20-40%)',
                                        6 => '6 - High: Affects many users (40-70%)',
                                        8 => '8 - Very High: Affects most users (70-90%)',
                                        10 => '10 - Maximum: Affects all users (>90%)'
                                    ])
                                    ->required()
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        $rice_score = ($state * $get('impact') * $get('confidence')) / ($get('effort') ?: 1);
                                        $set('rice_score', round($rice_score, 2));
                                    }),

                                Forms\Components\Select::make('impact')
                                    ->label('Impact (I)')
                                    ->options([
                                        1 => '1 - Minimal: Barely noticeable improvement',
                                        3 => '3 - Low: Small improvement',
                                        4 => '4 - Medium: Moderate improvement',
                                        6 => '6 - High: Significant improvement',
                                        8 => '8 - Very High: Major improvement',
                                        10 => '10 - Maximum: Game-changing improvement'
                                    ])
                                    ->required()
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        $rice_score = ($get('reach') * $state * $get('confidence')) / ($get('effort') ?: 1);
                                        $set('rice_score', round($rice_score, 2));
                                    }),

                                Forms\Components\Select::make('confidence')
                                    ->label('Confidence (C)')
                                    ->options([
                                        1 => '1 - Very Low: High uncertainty (20%)',
                                        3 => '3 - Low: Educated guess (40%)',
                                        4 => '4 - Medium: Somewhat confident (60%)',
                                        6 => '6 - High: Confident (80%)',
                                        8 => '8 - Very High: Very confident (90%)',
                                        10 => '10 - Maximum: Certain (100%)'
                                    ])
                                    ->required()
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        $rice_score = ($get('reach') * $get('impact') * $state) / ($get('effort') ?: 1);
                                        $set('rice_score', round($rice_score, 2));
                                    }),

                                Forms\Components\Select::make('effort')
                                    ->label('Effort (E)')
                                    ->options([
                                        1 => '1 - Minimal: Hours of work',
                                        3 => '3 - Low: A day of work',
                                        5 => '5 - Medium: Several days of work',
                                        7 => '7 - High: A week or more',
                                        10 => '10 - Very High: A month or more'
                                    ])
                                    ->required()
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        $rice_score = ($get('reach') * $get('impact') * $get('confidence')) / ($state ?: 1);
                                        $set('rice_score', round($rice_score, 2));
                                    }),
                            ]),

                        Forms\Components\TextInput::make('rice_score')
                            ->label('RICE Score')
                            ->disabled()
                            ->dehydrated(),
                    ]),
