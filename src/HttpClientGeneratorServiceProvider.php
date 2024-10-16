<?php

namespace Jcergolj\HttpClientGenerator;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Jcergolj\HttpClientGenerator\Console\Commands\CreateAllRequestStubsCommand;
use Jcergolj\HttpClientGenerator\Console\Commands\CreateAttributeStubsCommand;
use Jcergolj\HttpClientGenerator\Console\Commands\CreateBadResponseStubsCommand;
use Jcergolj\HttpClientGenerator\Console\Commands\CreateClientMacroStubsCommand;
use Jcergolj\HttpClientGenerator\Console\Commands\CreateHasStatusTraitStubsCommand;
use Jcergolj\HttpClientGenerator\Console\Commands\CreateRequestStubsCommand;
use Jcergolj\HttpClientGenerator\Console\Commands\CreateResponseStubsCommand;

class HttpClientGeneratorServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register(): void
    {
        //
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
        ]);
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
        ];
    }
}
