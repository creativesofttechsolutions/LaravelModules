<?php

namespace CreativeSoftTechSolutions\LaravelModules;

use CreativeSoftTechSolutions\LaravelModules\Commands\MakeModule;
use CreativeSoftTechSolutions\LaravelModules\Commands\ModuleMakeController;
use CreativeSoftTechSolutions\LaravelModules\Commands\ModuleMakeLivewire;
use CreativeSoftTechSolutions\LaravelModules\Commands\ModuleMakeMiddleware;
use CreativeSoftTechSolutions\LaravelModules\Commands\ModuleMakeMigration;
use CreativeSoftTechSolutions\LaravelModules\Commands\ModuleMakeModel;
use CreativeSoftTechSolutions\LaravelModules\Commands\ModuleMakeRule;
use CreativeSoftTechSolutions\LaravelModules\Facades\Module;
use CreativeSoftTechSolutions\LaravelModules\Services\ModuleService;
use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        // dd('ModuleServiceProvider booted!', config('modules.path'));

        // Publish config
        $this->publishes([
            __DIR__ . '/../config/modules.php' => config_path('modules.php'),
        ], 'config');

        $this->app->singleton('Module', function () {
            return new ModuleService();
        });
        $this->app->alias('Module', Module::class);

        // Load helpers
        if (file_exists(__DIR__ . '/Helpers/helpers.php')) {
            require_once __DIR__ . '/Helpers/helpers.php';
        }
    }

    /**
     * Register any application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/modules.php', 'modules');

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeModule::class,
                ModuleMakeController::class,
                ModuleMakeLivewire::class,
                ModuleMakeMiddleware::class,
                ModuleMakeMigration::class,
                ModuleMakeModel::class,
                ModuleMakeRule::class,
            ]);
        }
    }
}
