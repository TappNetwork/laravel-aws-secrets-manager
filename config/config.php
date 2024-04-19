<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AWS Region where secrets are stored
    |--------------------------------------------------------------------------
    |
    | The AWS Region where secrets are stored.
    |
    */

    'region' => env('AWS_DEFAULT_REGION'),
    'enable-secrets-manager' => env('ENABLE_SECRETS_MANAGER', false),

    /*
    |--------------------------------------------------------------------------
    | Tag used to return list of Secrets
    |--------------------------------------------------------------------------
    |
    | All the secrets with the `dev` tag will be loaded into environment variables.
    |
    */

    'tag-name' => env('AWS_SECRETS_TAG_NAME', 'stage'),
    'tag-value' => env('AWS_SECRETS_TAG_VALUE', 'dev'),

    /*
    |--------------------------------------------------------------------------
    | Environments where this service provider is enabled
    |--------------------------------------------------------------------------
    |
    | List the environment names in an array where this package should be enabled,
    | it will be compared against env('APP_ENV') set in the app.yaml file.
    |
    */

    'enabled-environments' => [
        'production',
    ],

    /*
    |--------------------------------------------------------------------------
    | Variables that require overwriting the config
    |--------------------------------------------------------------------------
    |
    | Some (not all) variables are set into the config, as such updating the env() will not overwrite
    | the config cached values. The variables below will overwrite the config.
    |
    */
    'variables-config' => [
        'APP_KEY' => 'app.key',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Enabled
    |--------------------------------------------------------------------------
    |
    | Boolean if you would like to enable cache. Datastore requests can add an additional 100-250ms
    | of latency to each request. It is recommended to use caching to significantly reduce this latency.
    |
    */

    'cache-enabled' => true, // boolean

    /*
    |--------------------------------------------------------------------------
    | Cache Expiry
    |--------------------------------------------------------------------------
    |
    | The length of time that the Cache should be enabled for in minutes. 30-60 minutes is recommended.
    |
    */

    'cache-expiry' => 30, // minutes

    /*
    |--------------------------------------------------------------------------
    | Cache Store
    |--------------------------------------------------------------------------
    |
    | Define the cache store that you wish to use (this must be configured in your config.cache file).
    | Note: you can only use a store that does not require credentials to access it. As such file is suggested.
    |
    */

    'cache-store' => 'file',

    /*
    |--------------------------------------------------------------------------
    | Key rotation
    |--------------------------------------------------------------------------
    |
    | If key rotation is enabled, force retrieving config if NextRotationDate is in the past
    |
    */

    'key-rotation' => env('AWS_SECRETS_KEY_ROTATION', false),

    /*
    |--------------------------------------------------------------------------
    | Debugging
    |--------------------------------------------------------------------------
    |
    | Enable debugging, latency introduced by this package on bootstrapping is calculated and logged
    | to the system log (viewable in stackdriver).
    |
    */

    'debug' => env('APP_DEBUG', false),

];
