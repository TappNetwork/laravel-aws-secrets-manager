<?php

namespace Tapp\LaravelAwsSecretsManager\Tests;

use Orchestra\Testbench\TestCase;
use Tapp\LaravelAwsSecretsManager\LaravelAwsSecretsManagerServiceProvider;

class ExampleTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [LaravelAwsSecretsManagerServiceProvider::class];
    }
}
