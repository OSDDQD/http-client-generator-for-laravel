<?php

namespace Osddqd\HttpClientGenerator\Tests;

use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as Orchestra;
use Osddqd\HttpClientGenerator\HttpClientGeneratorServiceProvider;

abstract class TestCase extends Orchestra
{
    use WithWorkbench;

    /**
     * Получить сервис-провайдеры пакета
     */
    protected function getPackageProviders($app): array
    {
        return [
            HttpClientGeneratorServiceProvider::class,
        ];
    }
}
