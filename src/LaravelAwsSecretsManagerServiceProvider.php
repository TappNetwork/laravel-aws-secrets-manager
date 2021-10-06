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
                __DIR__.'/../config/config.php' => base_path('config/aws-secrets-manager.php'),
            ], 'config');
        }

        // Load Secrets
        if (config('aws-secrets-manager.enable-secrets-manager')) {
            $secretsManager = new LaravelAwsSecretsManager();
            $secretsManager->loadSecrets();
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        //
    }
}
