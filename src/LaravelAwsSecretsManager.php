<?php
declare(strict_types = 1);

namespace Tapp\LaravelAwsSecretsManager;

use Aws\SecretsManager\SecretsManagerClient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

final class LaravelAwsSecretsManager
{
    private $client;
    private $configVariables;
    private $cache;
    private $cacheExpiry;
    private $cacheStore;
    private $debug;
    private $enabledEnvironments;
    private $listTagName;
    private $listTagValue;


    public function __construct(SecretsManagerClient $client)
    {
        $this->listTagName = config('aws-secrets-manager.tag-name');
        $this->listTagValue = config('aws-secrets-manager.tag-value');

        $this->configVariables = config('aws-secrets-manager.variables-config');

        $this->cache = config('aws-secrets-manager.cache-enabled', true);

        $this->cacheExpiry = config('aws-secrets-manager.cache-expiry', 0);

        $this->cacheStore = config('aws-secrets-manager.cache-store', 'file');

        $this->enabledEnvironments = config('aws-secrets-manager.enabled-environments', []);

        $this->debug = config('aws-secrets-manager.debug', false);

        $this->client = $client;
    }

    public function loadSecrets(): void
    {
        //load vars from datastore to env
        if ($this->debug) {
            $start = microtime(true);
        }

        //Only run this if the evironment is enabled in the config
        if (in_array(config('app.env'), $this->enabledEnvironments)) {
            if (! $this->checkCache()) {
                //Cache has expired need to refresh the cache from Datastore
                $this->getVariables();
            }

            //Process variables in config that need updating
            $this->updateConfigs();
        }

        if ($this->debug) {
            $time_elapsed_secs = microtime(true) - $start;
            error_log('Datastore secret request time: '.$time_elapsed_secs);
        }
    }

    protected function checkCache(): bool
    {
        foreach ($this->configVariables as $variable => $configPath) {
            $val = Cache::store($this->cacheStore)->get($variable);
            if (! is_null($val)) {
                putenv("$variable=$val");
            } else {
                return false;
            }
        }

        return true;
    }

    protected function getVariables(): void
    {
        try {
            $secrets = $this->client->listSecrets([
                'Filters' => [
                    [
                        'Key' => 'tag-key',
                        'Values' => [$this->listTagName],
                    ],
                    [
                        'Key' => 'tag-value',
                        'Values' => [$this->listTagValue],
                    ],
                ],
                'MaxResults' => 100,
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return;
        }

        foreach ($secrets['SecretList'] as $secret) {
            if (isset($secret['ARN'])) {
                $result = $this->client->getSecretValue([
                    'SecretId' => $secret['ARN'],
                ]);
                $key = $result['Name'];
                $secret = $result['SecretString'];
                putenv("$key=$secret");
                $this->storeToCache($result['Name'], $result['SecretString']);
            }
        }
    }

    protected function updateConfigs(): void
    {
        foreach ($this->configVariables as $variable => $configPath) {
            config([$configPath => env($variable)]);
        }
    }

    protected function storeToCache(string $name, string $val): void
    {
        if ($this->cache) {
            Cache::store($this->cacheStore)->put($name, $val, now()->addMinutes($this->cacheExpiry));
        }
    }
}
