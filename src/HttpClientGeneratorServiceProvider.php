<?php

namespace Osddqd\HttpClientGenerator;

use Illuminate\Support\ServiceProvider;
use Osddqd\HttpClientGenerator\Console\Commands\CreateAllRequestStubsCommand;
use Osddqd\HttpClientGenerator\Console\Commands\CreateAttributeStubsCommand;
use Osddqd\HttpClientGenerator\Console\Commands\CreateBadResponseStubsCommand;
use Osddqd\HttpClientGenerator\Console\Commands\CreateFactoryStubsCommand;
use Osddqd\HttpClientGenerator\Console\Commands\CreateRequestStubsCommand;
use Osddqd\HttpClientGenerator\Console\Commands\CreateResponseStubsCommand;

class HttpClientGeneratorServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        // Publish the configuration file
        $this->publishes([
            __DIR__.'/../config/http-client-generator.php' => config_path('http-client-generator.php'),
        ], 'config');

        // Publish the stub files
        $this->publishes([
            __DIR__.'/stubs' => resource_path('stubs/http-client-generator'),
        ], 'stubs');

        // Register console commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateAttributeStubsCommand::class,
                CreateRequestStubsCommand::class,
                CreateResponseStubsCommand::class,
                CreateBadResponseStubsCommand::class,
                CreateFactoryStubsCommand::class,
                CreateAllRequestStubsCommand::class,
            ]);
        }
    }

    /**
     * Register any package services.
     */
    public function register(): void
    {
        // Merge package configuration with application configuration
        // This allows users to override only specific options in their published config
        $this->mergeConfigFrom(
            __DIR__.'/../config/http-client-generator.php',
            'http-client-generator',
        );
    }
}
