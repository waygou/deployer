<?php

namespace Waygou\Deployer;

use Laravel\Passport\Passport;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Waygou\Deployer\Commands\DeployCommand;
use Waygou\Deployer\Commands\InstallLocalCommand;
use Waygou\Deployer\Commands\InstallRemoteCommand;
use Waygou\Deployer\Commands\LocalInstallConfigCommand;
use Laravel\Passport\Http\Middleware\CheckClientCredentials;

class DeployerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Schema::defaultStringLength(191);
        $this->publishConfiguration();
        $this->registerMigrations();
        $this->loadRemoteRoutes();
        $this->registerAuthGuard();
        $this->registerStorage();
    }

    private function registerStorage()
    {
        $this->app['config']->set('filesystems.disks', [
            'deployer' => [
                'driver' => 'local',
                'root' => app('config')->get('deployer.storage.path'),
            ],
        ]);
    }

    private function registerAuthGuard()
    {
        $this->app['config']->set('auth.providers.deployer_locals', [
            'driver' => 'eloquent',
            'model' => Local::class,
        ]);

        $this->app['config']->set('auth.guards.deployer', [
            'driver' => 'passport',
            'provider' => 'deployer_locals',
        ]);
    }

    protected function registerMigrations()
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }
    }

    protected function publishConfiguration()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
            __DIR__.'/../configuration/deployer.php' => config_path('deployer.php'),
            ], 'deployer-configuration');
        }
    }

    protected function loadRemoteRoutes()
    {
        if (config('deployer.type') == 'remote') {
            // Load Deployer routes using the api middleware.
            Route::as('deployer.')
                 ->middleware('same-token', 'client')
                 ->namespace('Waygou\Deployer\Http\Controllers')
                 ->prefix(app('config')->get('deployer.remote.prefix'))
                 ->group(__DIR__.'/../routes/api.php');

            // Load Laravel Passport routes.
            Passport::routes();
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../configuration/deployer.php',
            'deployer'
        );

        $this->commands([
            InstallRemoteCommand::class,
            InstallLocalCommand::class,
            LocalInstallConfigCommand::class,
            DeployCommand::class,
        ]);

        app('router')->aliasMiddleware(
            'client',
            CheckClientCredentials::class
        );

        app('router')->aliasMiddleware(
            'is-json',
            \Waygou\Deployer\Middleware\IsJson::class
        );

        app('router')->aliasMiddleware(
            'same-token',
            \Waygou\Deployer\Middleware\SameToken::class
        );
    }
}
