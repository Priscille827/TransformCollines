<?php

namespace App\Providers;

use App\Models\Disponibilite;
use App\Observers\DisponibiliteObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Disponibilite::observe(DisponibiliteObserver::class);
    }
}