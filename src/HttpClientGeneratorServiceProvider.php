<?php

namespace Osddqd\HttpClientGenerator;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Osddqd\HttpClientGenerator\Console\Commands\CreateAttributeStubsCommand;
use Osddqd\HttpClientGenerator\Console\Commands\CreateRequestStubsCommand;
use Osddqd\HttpClientGenerator\Console\Commands\CreateResponseStubsCommand;
use Osddqd\HttpClientGenerator\Console\Commands\CreateBadResponseStubsCommand;
use Osddqd\HttpClientGenerator\Console\Commands\CreateAllRequestStubsCommand;
use Osddqd\HttpClientGenerator\Console\Commands\CreateClientMacroStubsCommand;
use Osddqd\HttpClientGenerator\Console\Commands\CreateHasStatusTraitStubsCommand;
use Osddqd\HttpClientGenerator\Console\Commands\ListMacrosCommand;
use Osddqd\HttpClientGenerator\Console\Commands\ClearMacrosCacheCommand;
use Osddqd\HttpClientGenerator\Console\Commands\InstallCommand;

class HttpClientGeneratorServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/http-client-generator.php' => config_path('http-client-generator.php'),
        ], 'http-client-generator-config');

        $this->publishes([
            __DIR__ . '/stubs' => resource_path('stubs/http-client-generator'),
        ], 'http-client-generator-stubs');

        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateAttributeStubsCommand::class,
                CreateRequestStubsCommand::class,
                CreateResponseStubsCommand::class,
                CreateBadResponseStubsCommand::class,
                CreateAllRequestStubsCommand::class,
                CreateClientMacroStubsCommand::class,
                CreateHasStatusTraitStubsCommand::class,
                ListMacrosCommand::class,
                ClearMacrosCacheCommand::class,
                InstallCommand::class,
            ]);
        }

        // Автоматическая регистрация макросов (только не в тестовой среде)
        if (! app()->environment('testing')) {
            $this->registerHttpClientMacros();
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/http-client-generator.php',
            'http-client-generator',
        );
    }

    /**
     * Автоматическая регистрация HTTP клиентских макросов
     */
    public function registerHttpClientMacros(): void
    {
        if (! config('http-client-generator.auto_register.enabled', true)) {
            return;
        }

        $cacheKey = 'http_client_generator.macros';
        $cacheTtl = config('http-client-generator.auto_register.cache_ttl', 3600);

        $macros = Cache::remember($cacheKey, $cacheTtl, function () {
            return $this->discoverMacros();
        });

        // Отладочная информация для тестов
        if (app()->environment('testing')) {
            \Log::info('Discovered macros: ' . json_encode($macros));
        }

        foreach ($macros as $macroClass) {
            if (class_exists($macroClass)) {
                $macroInstance = new $macroClass();
                $methodName = $this->getMacroMethodName($macroClass);

                if (method_exists($macroInstance, $methodName)) {
                    Http::mixin($macroInstance);
                } else {
                    // Отладочная информация для тестов
                    if (app()->environment('testing')) {
                        throw new \Exception("Method {$methodName} not found in {$macroClass}");
                    }
                }
            } else {
                // Отладочная информация для тестов
                if (app()->environment('testing')) {
                    throw new \Exception("Class {$macroClass} not found");
                }
            }
        }
    }

    /**
     * Поиск макросов в директории клиентов
     */
    protected function discoverMacros(): array
    {
        $baseNamespace = config('http-client-generator.namespace.base', 'App\\Http\\Clients');
        $basePath = config('http-client-generator.paths.base', 'app/Http/Clients');

        $clientsPath = base_path($basePath);
        $macros = [];

        if (! is_dir($clientsPath)) {
            return $macros;
        }

        $clientDirectories = glob($clientsPath . '/*', GLOB_ONLYDIR);

        foreach ($clientDirectories as $clientDir) {
            $clientName = basename($clientDir);
            $macroFile = $clientDir . '/' . $clientName . 'Macro.php';

            if (file_exists($macroFile)) {
                $macroClass = $baseNamespace . '\\' . $clientName . '\\' . $clientName . 'Macro';
                $macros[] = $macroClass;
            }
        }

        return $macros;
    }

    /**
     * Получить имя метода макроса из класса
     */
    protected function getMacroMethodName(string $macroClass): string
    {
        // Извлекаем имя клиента из класса макроса
        // Например: App\Http\Clients\Twitter\TwitterMacro -> twitter
        $parts = explode('\\', $macroClass);
        $className = end($parts);
        $clientName = str_replace('Macro', '', $className);

        return strtolower($clientName);
    }
}
