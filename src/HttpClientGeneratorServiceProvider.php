<?php

namespace Osddqd\HttpClientGenerator;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Osddqd\HttpClientGenerator\Console\Commands\CreateAllRequestStubsCommand;
use Osddqd\HttpClientGenerator\Console\Commands\CreateAttributeStubsCommand;
use Osddqd\HttpClientGenerator\Console\Commands\CreateBadResponseStubsCommand;
use Osddqd\HttpClientGenerator\Console\Commands\CreateClientMacroStubsCommand;
use Osddqd\HttpClientGenerator\Console\Commands\CreateHasStatusTraitStubsCommand;
use Osddqd\HttpClientGenerator\Console\Commands\CreateRequestStubsCommand;
use Osddqd\HttpClientGenerator\Console\Commands\CreateResponseStubsCommand;
use Osddqd\HttpClientGenerator\Console\Commands\InstallCommand;

class HttpClientGeneratorServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/http-client-generator.php',
            'http-client-generator'
        );
    }

    public function boot(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            CreateAllRequestStubsCommand::class,
            CreateAttributeStubsCommand::class,
            CreateRequestStubsCommand::class,
            CreateResponseStubsCommand::class,
            CreateHasStatusTraitStubsCommand::class,
            CreateBadResponseStubsCommand::class,
            CreateClientMacroStubsCommand::class,
            InstallCommand::class,
        ]);

        $this->publishes([
            __DIR__ . '/config/http-client-generator.php' => config_path('http-client-generator.php'),
        ], 'config');

        $this->publishes([
            __DIR__ . '/config/http-client-generator.php' => config_path('http-client-generator.php'),
        ], 'http-client-generator-config');
    }

    public function provides(): array
    {
        return [
            CreateAllRequestStubsCommand::class,
            CreateAttributeStubsCommand::class,
            CreateRequestStubsCommand::class,
            CreateResponseStubsCommand::class,
            CreateHasStatusTraitStubsCommand::class,
            CreateBadResponseStubsCommand::class,
            CreateClientMacroStubsCommand::class,
            InstallCommand::class,
        ];
    }
}
