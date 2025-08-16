<?php

namespace Osddqd\HttpClientGenerator\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Osddqd\HttpClientGenerator\HttpClientGeneratorServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app): array
    {
        return [
            HttpClientGeneratorServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Настройка тестового окружения
        $app['config']->set('database.default', 'testing');
    }
}