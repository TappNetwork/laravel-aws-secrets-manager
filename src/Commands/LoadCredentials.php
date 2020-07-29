<?php

namespace Tapp\LaravelAwsSecretsManager\Commands;

use Illuminate\Console\Command;

class LoadCredentials extends Command
{
    protected $signature = 'aws-secrets:load-secrets';

    protected $description = 'Load secrets from AWS Secrets Manager';

    public function handle()
    {
        if (app('aws-secrets')->loadSecrets()) {
            $this->info('AWS Secrets Loaded.');
        } else {
            $this->error('Unable to load secrets.');
        }
    }
}
