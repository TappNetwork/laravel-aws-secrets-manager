<?php

namespace Tapp\LaravelAwsSecretsManager;

use Aws\Exception\AwsException;
use Aws\SecretsManager\SecretsManagerClient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class LaravelAwsSecretsManager
{
    protected $app;
    protected $client;
    protected $configVariables;
    protected $cache;
    protected $cacheExpiry;
    protected $cacheStore;
    protected $debug;
    protected $enabledEnvironments;
    protected $listTag;
    protected $variables;

    public function __construct($app)
    {
        $this->app = $app;

        $this->variables = config('aws-secrets-manager.variables');

        $this->listTagName = config('aws-secrets-manager.tag-name');
        $this->listTagValue = config('aws-secrets-manager.tag-value');

        $this->configVariables = config('aws-secrets-manager.variables-config');

        $this->cache = config('aws-secrets-manager.cache-enabled', true);

        $this->cacheExpiry = config('aws-secrets-manager.cache-expiry', 0);

        $this->cacheStore = config('aws-secrets-manager.cache-store', 'file');

        $this->enabledEnvironments = config('aws-secrets-manager.enabled-environments', []);

        $this->debug = config('aws-secrets-manager.debug', false);

        $this->client = new SecretsManagerClient([
            'version' => '2017-10-17',
            'region' => config('aws-secrets-manager.region'),
        ]);
    }

    public function loadSecrets()
    {
        //load vars from datastore to env
        if ($this->debug) {
            $start = microtime(true);
        }

        //Only run this if the evironment is enabled in the config
        if (in_array(config('app.env'), $this->enabledEnvironments)) {
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

        return true;
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
        } catch (AwsException $e) {
            $error = $e->getAwsErrorCode();
            Log::error($e);

            return false;
        } catch (\Exception $e) {
            report($e);

            return false;
        }

        foreach ($secrets as $secret) {
            foreach ($secret as $item) {
                if (isset($item['ARN'])) {
                    try {
                        $result = $this->client->getSecretValue([
                            'SecretId' => $item['ARN'],
                        ]);
                    } catch (AwsException $e) {
                        $error = $e->getAwsErrorCode();
                        Log::error($e);

                        return false;
                    } catch (\Exception $e) {
                        report($e);

                        return false;
                    }


                    $secretValues = json_decode($result['SecretString'], true);
                    if (is_array($secretValues)) {
                        foreach ($secretValues as $key => $secret) {
                            putenv("$key=$secret");
                            $this->storeToCache($key, $secret);
                        }
                    }
                }
            }
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
