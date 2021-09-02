<?php

namespace Bigmom\SceneEditor\Providers;

use Bigmom\SceneEditor\Commands\PullScenes;
use Illuminate\Support\ServiceProvider;

class SceneEditorServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../migrations');
            
            $this->publishes([
                __DIR__.'/../config/scene-editor.php' => config_path('scene-editor.php'),
            ], 'config');

            $this->commands([
                PullScenes::class,
            ]);
        }
    }
}
