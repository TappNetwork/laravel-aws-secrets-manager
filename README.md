# AWS Secrets Manager

[![Latest Version on Packagist](https://img.shields.io/packagist/v/tapp/laravel-aws-secrets-manager.svg?style=flat-square)](https://packagist.org/packages/tapp/laravel-aws-secrets-manager)
![Tests Laravel 10](https://github.com/TappNetwork/laravel-aws-secrets-manager/actions/workflows/run-tests-l10.yml/badge.svg)
[![Total Downloads](https://img.shields.io/packagist/dt/tapp/laravel-aws-secrets-manager.svg?style=flat-square)](https://packagist.org/packages/tapp/laravel-aws-secrets-manager)

Manage environment secrets using AWS Secrets Manager.

## Installation

You can install the package via composer:

```bash
composer require tapp/laravel-aws-secrets-manager
```

Publish Config:
```
php artisan vendor:publish --provider="Tapp\LaravelAwsSecretsManager\LaravelAwsSecretsManagerServiceProvider"
```

## Usage

This package will try and load in secrets from AWS Secrets manager in any environment that is in the `enabled-environments` config array.  It is recommended that caching is enabled to reduce round trips to AWS Secrets Manager.

Available env values:
``` php
AWS_DEFAULT_REGION
AWS_SECRETS_TAG_NAME=stage
AWS_SECRETS_TAG_VALUE=production
```

`AWS_SECRETS_TAG_NAME` and `AWS_SECRETS_TAG_VALUE` are used to pull down all the secrets that match the tag key/value.

### Other Environment-based Configuration

#### Enabled environments

Specify which environments should have AWS Secrets enabled:

`AWS_SECRETS_ENABLED_ENV=production,staging`

Default: `production`

#### Overwritable Variables Config

Specify which variables should be able to overwrite the config using the `AWS_SECRETS_VARIABLES_CONFIGS` key in the `.env` file. The format is a comma-separated list of `ENV_VARIABLE_NAME:CONFIG_KEY` pairs.

For example:

`VARIABLES_CONFIG_KEYS=APP_KEY:app.key,OTHER_KEY:app.other_key`

This setup allows `APP_KEY` to overwrite `app.key` in the config, and `OTHER_KEY` to overwrite `app.other_key`.

Default Behavior: If `AWS_SECRETS_VARIABLES_CONFIGS` is not set or is empty, no variables will be set for config overwriting.

#### Cache Settings

For example:
```
AWS_SECRETS_CACHE_ENABLED=true
AWS_SECRETS_CACHE_EXPIRY=60
AWS_SECRETS_CACHE_STORE=file
```

### Setting up AWS Secrets

1. Store New Secret.
1. Select type of secret, one of AWS managed or other.
1. Enter Key/Value, the KEY should match a env variable.
1. Give it a secret name and description
1. Add a tag key/value (stage => production) is an example if you want to pull down all production secrets.

### Cache the config
```
php artisan config:cache
```

### AWS Credentials

Since this package utilizes the PHP AWS SDK the following .env values are used or credentials set ~/.aws/credentials.

```
AWS_ACCESS_KEY_ID
AWS_SECRET_ACCESS_KEY
```
[https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/guide_credentials.html](https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/guide_credentials.html)

### Key Rotation
If key rotation is enabled, the most recent next rotation date is cached and if it's in the past we force getting the secrets.

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email security@tappnetwork.com instead of using the issue tracker.

## Credits

- [Steve Williamson](https://github.com/tapp)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).

## Laravel Google App Engine (GAE) Datastore Secret Manager

This package was heavily based off of the GAE package. [laravel-GAE-secret-manager](https://github.com/tommerrett/laravel-GAE-secret-manager).
