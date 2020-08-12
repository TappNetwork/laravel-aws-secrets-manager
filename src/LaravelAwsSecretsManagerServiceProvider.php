<?php

namespace Tapp\LaravelAwsSecretsManager;

use Aws\SecretsManager\SecretsManagerClient;
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

        $client = new SecretsManagerClient([
            'version' => '2017-10-17',
            'region' => config('aws-secrets-manager.region'),
        ]);

        // Load Secrets
        $secretsManager = new LaravelAwsSecretsManager($client);
        $secretsManager->loadSecrets();
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        //
    }
}
