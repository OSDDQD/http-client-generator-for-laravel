<?php

namespace osddqd\HttpClientGenerator;

use Illuminate\Support\ServiceProvider;
use osddqd\HttpClientGenerator\Console\Commands\CreateAllRequestStubsCommand;
use osddqd\HttpClientGenerator\Console\Commands\CreateAllTestsCommand;
use osddqd\HttpClientGenerator\Console\Commands\CreateAttributeStubsCommand;
use osddqd\HttpClientGenerator\Console\Commands\CreateAttributeTestCommand;
use osddqd\HttpClientGenerator\Console\Commands\CreateBadResponseStubsCommand;
use osddqd\HttpClientGenerator\Console\Commands\CreateBadResponseTestCommand;
use osddqd\HttpClientGenerator\Console\Commands\CreateFactoryStubsCommand;
use osddqd\HttpClientGenerator\Console\Commands\CreateFactoryTestCommand;
use osddqd\HttpClientGenerator\Console\Commands\CreateRequestStubsCommand;
use osddqd\HttpClientGenerator\Console\Commands\CreateRequestTestCommand;
use osddqd\HttpClientGenerator\Console\Commands\CreateResponseStubsCommand;
use osddqd\HttpClientGenerator\Console\Commands\CreateResponseTestCommand;

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
                // Test commands
                CreateAttributeTestCommand::class,
                CreateRequestTestCommand::class,
                CreateResponseTestCommand::class,
                CreateBadResponseTestCommand::class,
                CreateFactoryTestCommand::class,
                CreateAllTestsCommand::class,
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
