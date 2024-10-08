<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Filament\Facades\Filament;
use App\Filament\Components\MetricsModal;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(125); // Set default string length to 125

        Filament::registerRenderHook(
            'body.end',
            fn (): string => Blade::render('@livewire(\'metrics-modal\')'),
        );
    }
}
