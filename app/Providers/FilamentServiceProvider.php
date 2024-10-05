<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Facades\Filament;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\Filament\UserPanelProvider;

class FilamentServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Filament::registerPanel(AdminPanelProvider::class);
        Filament::registerPanel(UserPanelProvider::class);
    }
}