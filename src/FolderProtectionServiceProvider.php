<?php

declare(strict_types=1);

namespace KommandHub\FolderProtection;

use Illuminate\Support\ServiceProvider;

class FolderProtectionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/folder-protection.php', 'folder-protection'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/folder-protection.php' => config_path('folder-protection.php'),
            ], 'folder-protection-config');
        }
    }
}
