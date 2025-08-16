<?php

namespace Osddqd\HttpClientGenerator\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class AppCommand extends Command
{
    /**
     * Get the configuration key for the given type.
     */
    protected function getTypeKey(string $type): string
    {
        // Преобразуем тип в правильный ключ конфигурации
        $typeMap = [
            'Attribute' => 'attributes',
            'Request' => 'requests',
            'Response' => 'responses',
            'Factory' => 'factories',
        ];

        return $typeMap[$type] ?? strtolower($type).'s';
    }

    /**
     * Get the namespace for the given client and type.
     */
    protected function getNamespace(string $client, string $type): string
    {
        $baseNamespace = $this->option('namespace') ?? config('http-client-generator.namespace.base', 'App\\Http\\Clients');
        $typeKey = $this->getTypeKey($type);
        $typeNamespace = config("http-client-generator.namespace.{$typeKey}", ucfirst($type).'s');

        return "{$baseNamespace}\\{$client}\\{$typeNamespace}";
    }

    /**
     * Get the file path for the class.
     */
    protected function getClassPath(string $client, string $name, string $type): string
    {
        $basePath = $this->option('path') ?? config('http-client-generator.paths.base', 'app/Http/Clients');
        $typeKey = $this->getTypeKey($type);
        $typePath = config("http-client-generator.namespace.{$typeKey}", ucfirst($type).'s');

        return base_path("{$basePath}/{$client}/{$typePath}/{$name}{$type}.php");
    }

    /**
     * Get the namespace for tests.
     */
    protected function getTestNamespace(string $client, string $type): string
    {
        // Используем конфигурацию namespace для тестов, если она есть
        $testsNamespace = config('http-client-generator.namespace.tests');

        if ($testsNamespace) {
            $typePath = ucfirst($type).'s';

            return "{$testsNamespace}\\{$client}\\{$typePath}";
        }

        // Fallback: генерируем namespace из пути
        $testsPath = $this->option('tests-path') ?? config('http-client-generator.paths.tests', 'tests/Unit/Http/Clients');
        $typePath = ucfirst($type).'s';

        // Преобразуем путь в namespace, заменяя слеши на обратные слеши и делая первую букву каждой части заглавной
        $pathParts = explode('/', trim($testsPath, '/'));
        $namespaceParts = array_map(function ($part) {
            // Заменяем подчеркивания и дефисы на пробелы, затем делаем каждое слово с заглавной буквы
            $part = str_replace(['_', '-'], ' ', $part);
            $part = ucwords($part);

            return str_replace(' ', '', $part);
        }, $pathParts);
        $testsNamespace = implode('\\', $namespaceParts);

        return "{$testsNamespace}\\{$client}\\{$typePath}";
    }

    /**
     * Get the file path for the test.
     */
    protected function getTestPath(string $client, string $name, string $type): string
    {
        $testsPath = $this->option('tests-path') ?? config('http-client-generator.paths.tests', 'tests/Unit/Http/Clients');
        $typeKey = $this->getTypeKey($type);
        $typePath = config("http-client-generator.namespace.{$typeKey}", ucfirst($type).'s');

        return base_path("{$testsPath}/{$client}/{$typePath}/{$name}{$type}Test.php");
    }

    /**
     * Get the stub file path.
     */
    protected function getStubPath(string $type): string
    {
        $customStubsPath = config('http-client-generator.stubs.custom_path');

        if ($customStubsPath && file_exists("{$customStubsPath}/{$type}.stub")) {
            return "{$customStubsPath}/{$type}.stub";
        }

        return __DIR__.'/../../stubs/Clients/'.$type.'.stub';
    }

    /**
     * Get the test stub file path.
     */
    protected function getTestStubPath(string $type): string
    {
        $customStubsPath = config('http-client-generator.stubs.custom_path');

        if ($customStubsPath && file_exists("{$customStubsPath}/Tests/{$type}.stub")) {
            return "{$customStubsPath}/Tests/{$type}.stub";
        }

        return __DIR__.'/../../stubs/Tests/'.$type.'.stub';
    }

    /**
     * Create a class stub file.
     */
    protected function createClassStub($client, $name, $type)
    {
        $classPath = $this->getClassPath($client, $name, $type);

        if (file_exists($classPath)) {
            $this->warn("{$name}{$type} class already exists. Skipping.");

            return;
        }

        $stubPath = $this->getStubPath($type);

        if (! file_exists($stubPath)) {
            $this->error("Stub file not found: {$stubPath}");

            return;
        }

        $file = file_get_contents($stubPath);
        $newStub = Str::of($file)
            ->replace($this->getReplacementVariables(), $this->getReplacementValues($client, $name, $type))
            ->toString();

        $directory = dirname($classPath);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents($classPath, $newStub);
        $this->info("{$type} class has been successfully created at: {$classPath}");
    }

    /**
     * Get replacement variables for stub files.
     */
    protected function getReplacementVariables(): array
    {
        return [
            '{{ namespace }}',
            '{{ client }}',
            '{{ name }}',
            '{{ base_namespace }}',
            '{{ attribute_namespace }}',
            '{{ request_namespace }}',
            '{{ response_namespace }}',
            '{{ test_namespace }}',
        ];
    }

    /**
     * Get replacement values for stub files.
     */
    protected function getReplacementValues($client, $name, $type): array
    {
        $baseNamespace = $this->option('namespace') ?? config('http-client-generator.namespace.base', 'App\\Http\\Clients');

        return [
            $this->getNamespace($client, $type),
            $client,
            $name,
            $baseNamespace,
            $this->getNamespace($client, 'Attribute'),
            $this->getNamespace($client, 'Request'),
            $this->getNamespace($client, 'Response'),
            $this->getTestNamespace($client, $type),
        ];
    }

    /**
     * Check if tests should be generated.
     */
    protected function shouldGenerateTests(): bool
    {
        // Если передана опция --no-tests, не генерируем тесты
        if ($this->option('no-tests')) {
            return false;
        }

        // Иначе используем глобальную настройку из конфигурации
        return config('http-client-generator.generate_tests', true);
    }

    /**
     * Create a test stub file.
     */
    protected function createTestStub($client, $name, $type)
    {
        // Проверяем, нужно ли генерировать тесты
        if (! $this->shouldGenerateTests()) {
            $this->info("Test generation skipped for {$name}{$type}.");

            return;
        }

        $testPath = $this->getTestPath($client, $name, $type);

        if (file_exists($testPath)) {
            $this->warn("{$name}{$type}Test class already exists. Skipping.");

            return;
        }

        $stubPath = $this->getTestStubPath($type);

        if (! file_exists($stubPath)) {
            $this->error("Test stub file not found: {$stubPath}");

            return;
        }

        $stub = file_get_contents($stubPath);
        $newStub = Str::of($stub)
            ->replace($this->getReplacementVariables(), $this->getReplacementValues($client, $name, $type))
            ->toString();

        $directory = dirname($testPath);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents($testPath, $newStub);
        $this->info("{$type}Test class has been successfully created at: {$testPath}");
    }
}
