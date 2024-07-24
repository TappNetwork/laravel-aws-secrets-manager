<?php

namespace Tapp\LaravelAwsSecretsManager\Tests;

use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Tapp\LaravelAwsSecretsManager\LaravelAwsSecretsManagerServiceProvider;

class ExampleTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [LaravelAwsSecretsManagerServiceProvider::class];
    }

    #[Test]
    public function true_is_true()
    {
        $this->assertTrue(true);
    }
}
