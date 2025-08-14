<?php

namespace Osddqd\HttpClientGenerator;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Osddqd\HttpClientGenerator\Console\Commands\ClearMacrosCacheCommand;
use Osddqd\HttpClientGenerator\Console\Commands\CreateAllRequestStubsCommand;
use Osddqd\HttpClientGenerator\Console\Commands\CreateAttributeStubsCommand;
use Osddqd\HttpClientGenerator\Console\Commands\CreateBadResponseStubsCommand;
use Osddqd\HttpClientGenerator\Console\Commands\CreateClientMacroStubsCommand;
use Osddqd\HttpClientGenerator\Console\Commands\CreateHasStatusTraitStubsCommand;
use Osddqd\HttpClientGenerator\Console\Commands\CreateRequestStubsCommand;
use Osddqd\HttpClientGenerator\Console\Commands\CreateResponseStubsCommand;
use Osddqd\HttpClientGenerator\Console\Commands\InstallCommand;
use Osddqd\HttpClientGenerator\Console\Commands\ListMacrosCommand;

class HttpClientGeneratorServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/http-client-generator.php',
            'http-client-generator',
        );
    }

    public function boot(): void
    {
        // Автоматическая регистрация макросов
        $this->registerHttpMacros();

        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            ClearMacrosCacheCommand::class,
            CreateAllRequestStubsCommand::class,
            CreateAttributeStubsCommand::class,
            CreateRequestStubsCommand::class,
            CreateResponseStubsCommand::class,
            CreateHasStatusTraitStubsCommand::class,
            CreateBadResponseStubsCommand::class,
            CreateClientMacroStubsCommand::class,
            InstallCommand::class,
            ListMacrosCommand::class,
        ]);

        $this->publishes([
            __DIR__ . '/config/http-client-generator.php' => config_path('http-client-generator.php'),
        ], 'config');

        $this->publishes([
            __DIR__ . '/config/http-client-generator.php' => config_path('http-client-generator.php'),
        ], 'http-client-generator-config');
    }

    /**
     * Автоматически регистрирует все найденные HTTP макросы
     */
    protected function registerHttpMacros(): void
    {
        // Проверяем, включена ли автоматическая регистрация
        if (! config('http-client-generator.auto_register.enabled', true)) {
            return;
        }

        $baseNamespace = config('http-client-generator.namespace.base', 'App\\Http\\Clients');
        $basePath = config('http-client-generator.paths.base', 'app/Http/Clients');

        // Получаем абсолютный путь к директории с клиентами
        $clientsPath = base_path($basePath);

        if (! is_dir($clientsPath)) {
            return;
        }

        // Используем кэш для улучшения производительности
        $cacheKey = 'http_client_generator.macros';
        $cacheTtl = config('http-client-generator.auto_register.cache_ttl', 3600);

        $macroClasses = cache()->remember($cacheKey, $cacheTtl, function () use ($clientsPath, $baseNamespace) {
            return $this->discoverMacroClasses($clientsPath, $baseNamespace);
        });

        // Регистрируем найденные макросы
        foreach ($macroClasses as $macroClass) {
            if (class_exists($macroClass)) {
                \Illuminate\Support\Facades\Http::mixin(new $macroClass);
            }
        }
    }

    /**
     * Обнаруживает все классы макросов в указанной директории
     */
    protected function discoverMacroClasses(string $clientsPath, string $baseNamespace): array
    {
        $macroClasses = [];

        // Сканируем директории клиентов
        $clientDirectories = glob($clientsPath . '/*', GLOB_ONLYDIR);

        foreach ($clientDirectories as $clientDir) {
            $clientName = basename($clientDir);
            $macroFile = $clientDir . '/' . $clientName . 'Macro.php';

            if (file_exists($macroFile)) {
                $macroClasses[] = $baseNamespace . '\\' . $clientName . '\\' . $clientName . 'Macro';
            }
        }

        return $macroClasses;
    }

    public function provides(): array
    {
        return [
            ClearMacrosCacheCommand::class,
            CreateAllRequestStubsCommand::class,
            CreateAttributeStubsCommand::class,
            CreateRequestStubsCommand::class,
            CreateResponseStubsCommand::class,
            CreateHasStatusTraitStubsCommand::class,
            CreateBadResponseStubsCommand::class,
            CreateClientMacroStubsCommand::class,
            InstallCommand::class,
            ListMacrosCommand::class,
        ];
    }
}
