<?php

namespace OwowAgency\Teams;

use Illuminate\Support\ServiceProvider;
use OwowAgency\Teams\Observers\InvitationObserver;

class TeamsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'teams');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('teams.php'),
            ], 'config');

            // Publishing the migration files.
            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'migrations');
        }

        config('teams.models.invitation')::observe(InvitationObserver::class);
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'teams');
    }
}
