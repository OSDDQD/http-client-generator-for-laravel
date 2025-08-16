<?php

namespace osddqd\HttpClientGenerator\Tests;

use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as Orchestra;
use osddqd\HttpClientGenerator\HttpClientGeneratorServiceProvider;

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
