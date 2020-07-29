<?php

namespace Tapp\LaravelAwsSecretsManager;

use Illuminate\Support\ServiceProvider;

class LaravelAwsSecretsManagerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('aws-secrets-manager.php'),
            ], 'config');
        }

        $this->commands([
            Commands\LoadCredentials::class,
        ]);
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'aws-secrets-manager');

        $this->app->singleton('aws-secrets', function ($app) {
            return new LaravelAwsSecretsManager($app);
        });
    }
}
