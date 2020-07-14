<?php

namespace Tapp\LaravelAwsSecretsManager;

use Illuminate\Support\Facades\Cache;

class LaravelAwsSecretsManager
{
    protected $client;
    protected $variables;
    protected $configVariables;
    protected $cache;
    protected $cacheExpiry;
    protected $cacheStore;
    protected $enabledEnvironments;
    protected $debug;

    public function __construct()
    {
        $this->variables = config('aws-secrets-manager.variables');

        $this->configVariables = config('aws-secrets-manager.variables-config');

        $this->cache = config('aws-secrets-manager.cache-enabled', true);

        $this->cacheExpiry = config('aws-secrets-manager.cache-expiry', 0);

        $this->cacheStore = config('aws-secrets-manager.cache-store', 'file');

        $this->enabledEnvironments = config('aws-secrets-manager.enabled-environments', []);

        $this->debug = config('aws-secrets-manager.debug', false);
    }

    public function loadSecrets()
    {
        //load vars from datastore to env
        if ($this->debug) {
            $start = microtime(true);
        }

        //Only run this if the evironment is enabled in the config
        if (in_array(env('APP_ENV'), $this->enabledEnvironments)) {
            if ($this->cache) {
                if (! $this->checkCache()) {
                    //Cache has expired need to refresh the cache from Datastore
                    $this->getVariables();
                }
            } else {
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

    protected function checkCache()
    {
        foreach ($this->variables as $variable) {
            $val = Cache::store($this->cacheStore)->get($variable);
            if (! is_null($val)) {
                putenv("$variable=$val");
            } else {
                return false;
            }
        }

        return true;
    }

    protected function getVariables()
    {
        try {
            $this->client = new SecretsManagerClient([
                'version' => '2017-10-17',
                'region' => config('aws-secrets-manager.debug.region'),
            ]);

            // TODO Update this to use AWS Secrets manager instead of GAE
            // $result = $this->client->getSecretValue([
            //     'SecretId' => $this->connection->secret_id,
            //     'VersionId' => $this->connection->version_id,
            // ]);


            $query = $datastore->query();
            $query->kind('Parameters');

            $res = $datastore->runQuery($query);
            foreach ($res as $parameter) {
                $name = $parameter['name'];
                $val = $parameter['value'];
                putenv("$name=$val");
                $this->storeToCache($name, $val);
            }
        } catch (\Exception $e) {
            // Nothing, this is normal
        }
    }

    protected function updateConfigs()
    {
        foreach ($this->configVariables as $variable => $configPath) {
            config([$configPath => env($variable)]);
        }
    }

    protected function storeToCache($name, $val)
    {
        if ($this->cache) {
            Cache::store($this->cacheStore)->put($name, $val, now()->addMinutes($this->cacheExpiry));
        }
    }
}
