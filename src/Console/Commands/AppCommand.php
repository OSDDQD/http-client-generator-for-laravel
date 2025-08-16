<?php

namespace osddqd\HttpClientGenerator\Console\Commands;

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
     * Get FQDN for the given client, name and type.
     */
    protected function getFqdn(string $client, string $name, string $type): string
    {
        $namespace = $this->getNamespace($client, $type);

        return "{$namespace}\\{$name}{$type}";
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

        // Используем новые команды для генерации тестов
        $this->createTestUsingNewCommand($client, $name, $type);
    }

    /**
     * Create test using new test generation commands.
     */
    protected function createTestUsingNewCommand($client, $name, $type)
    {
        $classFqdn = $this->getFqdn($client, $name, $type);

        // Проверяем, что класс существует
        if (! class_exists($classFqdn)) {
            $this->warn("Class {$classFqdn} does not exist. Cannot generate test.");

            return;
        }

        // Определяем команду для генерации теста
        $testCommand = $this->getTestCommand($type, $name);

        if (! $testCommand) {
            $this->warn("No test command available for type: {$type}");

            return;
        }

        // Собираем опции для передачи в команду
        $options = [];
        if ($this->option('test-namespace')) {
            $options['--test-namespace'] = $this->option('test-namespace');
        }

        // Вызываем команду для генерации теста
        try {
            $exitCode = \Illuminate\Support\Facades\Artisan::call($testCommand, array_merge([
                'class_fqdn' => $classFqdn,
            ], $options));

            if ($exitCode === 0) {
                $this->info("Test for {$type} class generated successfully.");
            } else {
                $this->warn("Test generation for {$type} class completed with warnings.");
            }
        } catch (\Exception $e) {
            $this->error("Failed to generate test for {$type} class: ".$e->getMessage());
        }
    }

    /**
     * Get test command name for the given type.
     */
    protected function getTestCommand($type, $name = null): ?string
    {
        // Для BadResponse используем специальную команду
        if ($type === 'Response' && $name === 'Bad') {
            return 'http-client-generator:test:bad-response';
        }

        $commands = [
            'Attribute' => 'http-client-generator:test:attribute',
            'Request' => 'http-client-generator:test:request',
            'Response' => 'http-client-generator:test:response',
            'Factory' => 'http-client-generator:test:factory',
        ];

        return $commands[$type] ?? null;
    }

    /**
     * Parse FQDN to extract components.
     */
    protected function parseFqdn(string $fqdn): array
    {
        $parts = explode('\\', $fqdn);
        $className = array_pop($parts);
        $namespace = implode('\\', $parts);

        // Извлекаем тип класса (Attribute, Request, Response, Factory)
        $type = null;
        $name = $className;

        if ($className === 'BadResponse') {
            $type = 'Response';
            $name = 'Bad';
        } elseif (str_ends_with($className, 'Attribute')) {
            $type = 'Attribute';
            $name = substr($className, 0, -9); // убираем 'Attribute'
        } elseif (str_ends_with($className, 'Request')) {
            $type = 'Request';
            $name = substr($className, 0, -7); // убираем 'Request'
        } elseif (str_ends_with($className, 'Response')) {
            $type = 'Response';
            $name = substr($className, 0, -8); // убираем 'Response'
        } elseif (str_ends_with($className, 'Factory')) {
            $type = 'Factory';
            $name = substr($className, 0, -7); // убираем 'Factory'
        }

        // Извлекаем client из namespace
        $namespaceParts = explode('\\', $namespace);
        $client = null;

        // Ищем client в структуре namespace
        // Например: App\Http\Clients\GitHub\Attributes -> client = GitHub
        $clientIndex = array_search('Clients', $namespaceParts);
        if ($clientIndex !== false && isset($namespaceParts[$clientIndex + 1])) {
            $client = $namespaceParts[$clientIndex + 1];
        }

        return [
            'namespace' => $namespace,
            'className' => $className,
            'type' => $type,
            'name' => $name,
            'client' => $client,
            'fullNamespace' => $fqdn,
        ];
    }

    /**
     * Get test namespace from class FQDN.
     */
    protected function getTestNamespaceFromFqdn(string $classFqdn): string
    {
        // Если указан кастомный namespace для тестов
        if ($this->option('test-namespace')) {
            $parsed = $this->parseFqdn($classFqdn);

            return $this->option('test-namespace').'\\'.$parsed['className'].'Test';
        }

        $parsed = $this->parseFqdn($classFqdn);

        // Используем конфигурацию namespace для тестов, если она есть
        $testsNamespace = config('http-client-generator.namespace.tests');

        if ($testsNamespace && $parsed['client'] && $parsed['type']) {
            $typeKey = $this->getTypeKey($parsed['type']);
            $typePath = config("http-client-generator.namespace.{$typeKey}", ucfirst($parsed['type']).'s');

            return "{$testsNamespace}\\Http\\Clients\\{$parsed['client']}\\{$typePath}";
        }

        // Fallback: преобразуем namespace класса в namespace теста
        $classNamespace = $parsed['namespace'];

        // Заменяем базовый namespace на тестовый
        $baseNamespace = config('http-client-generator.namespace.base', 'App\\Http\\Clients');
        $testsBasePath = config('http-client-generator.paths.tests', 'tests/Unit/Http/Clients');

        if (str_starts_with($classNamespace, $baseNamespace)) {
            $relativePath = substr($classNamespace, strlen($baseNamespace));
            $testNamespace = $this->pathToNamespace($testsBasePath).$relativePath;

            return $testNamespace;
        }

        // Если не удалось определить, используем Tests\Unit как базу
        return 'Tests\\Unit\\'.str_replace('App\\', '', $classNamespace);
    }

    /**
     * Get test file path from class FQDN.
     */
    protected function getTestPathFromFqdn(string $classFqdn): string
    {
        $parsed = $this->parseFqdn($classFqdn);

        // Если указан кастомный namespace для тестов
        if ($this->option('test-namespace')) {
            // Простой путь для кастомного namespace
            $testNamespace = $this->option('test-namespace');
            $testPath = $this->namespaceToPath($testNamespace).'/'.$parsed['className'].'Test.php';

            return base_path($testPath);
        }

        // Используем стандартную структуру тестов
        $testsPath = config('http-client-generator.paths.tests', 'tests/Unit');
        $typeKey = $this->getTypeKey($parsed['type']);
        $typePath = config("http-client-generator.namespace.{$typeKey}", ucfirst($parsed['type']).'s');

        if ($parsed['client']) {
            $testPath = "{$testsPath}/Http/Clients/{$parsed['client']}/{$typePath}/{$parsed['className']}Test.php";
        } else {
            $testPath = "{$testsPath}/{$typePath}/{$parsed['className']}Test.php";
        }

        return base_path($testPath);
    }

    /**
     * Convert namespace to file path.
     */
    protected function namespaceToPath(string $namespace): string
    {
        $parts = explode('\\', $namespace);
        $path = strtolower(array_shift($parts)); // tests или Tests -> tests

        foreach ($parts as $part) {
            $path .= '/'.$part;
        }

        return $path;
    }

    /**
     * Convert file path to namespace.
     */
    protected function pathToNamespace(string $path): string
    {
        $pathParts = explode('/', trim($path, '/'));
        $namespaceParts = array_map(function ($part) {
            // Заменяем подчеркивания и дефисы на пробелы, затем делаем каждое слово с заглавной буквы
            $part = str_replace(['_', '-'], ' ', $part);
            $part = ucwords($part);

            return str_replace(' ', '', $part);
        }, $pathParts);

        return implode('\\', $namespaceParts);
    }

    /**
     * Create a test stub file from FQDN.
     */
    protected function createTestStubFromFqdn(string $classFqdn, string $expectedType)
    {
        $parsed = $this->parseFqdn($classFqdn);

        if ($parsed['type'] !== $expectedType) {
            $this->error("Expected {$expectedType} class, but got {$parsed['type']} class.");

            return;
        }

        $testPath = $this->getTestPathFromFqdn($classFqdn);

        if (file_exists($testPath)) {
            $this->warn("{$parsed['className']}Test class already exists. Skipping.");

            return;
        }

        $stubPath = $this->getTestStubPath($parsed['type']);

        if (! file_exists($stubPath)) {
            $this->error("Test stub file not found: {$stubPath}");

            return;
        }

        $stub = file_get_contents($stubPath);
        $newStub = Str::of($stub)
            ->replace($this->getReplacementVariables(), $this->getReplacementValuesFromFqdn($classFqdn))
            ->toString();

        $directory = dirname($testPath);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents($testPath, $newStub);
        $this->info("{$parsed['type']}Test class has been successfully created at: {$testPath}");
    }

    /**
     * Get replacement values for stub files from FQDN.
     */
    protected function getReplacementValuesFromFqdn(string $classFqdn): array
    {
        $parsed = $this->parseFqdn($classFqdn);

        return [
            $parsed['namespace'], // {{ namespace }} - namespace исходного класса
            $parsed['client'] ?? 'Unknown', // {{ client }}
            $parsed['name'] ?? 'Unknown', // {{ name }}
            $this->getBaseNamespaceFromFqdn($classFqdn), // {{ base_namespace }}
            $this->getAttributeNamespaceFromFqdn($classFqdn), // {{ attribute_namespace }}
            $this->getRequestNamespaceFromFqdn($classFqdn), // {{ request_namespace }}
            $this->getResponseNamespaceFromFqdn($classFqdn), // {{ response_namespace }}
            $this->getTestNamespaceFromFqdn($classFqdn), // {{ test_namespace }}
        ];
    }

    /**
     * Get base namespace from FQDN.
     */
    protected function getBaseNamespaceFromFqdn(string $classFqdn): string
    {
        $parsed = $this->parseFqdn($classFqdn);
        $namespaceParts = explode('\\', $parsed['namespace']);

        // Ищем индекс 'Clients'
        $clientsIndex = array_search('Clients', $namespaceParts);
        if ($clientsIndex !== false) {
            return implode('\\', array_slice($namespaceParts, 0, $clientsIndex + 1));
        }

        return config('http-client-generator.namespace.base', 'App\\Http\\Clients');
    }

    /**
     * Get attribute namespace from FQDN.
     */
    protected function getAttributeNamespaceFromFqdn(string $classFqdn): string
    {
        $baseNamespace = $this->getBaseNamespaceFromFqdn($classFqdn);
        $parsed = $this->parseFqdn($classFqdn);

        if ($parsed['client']) {
            return "{$baseNamespace}\\{$parsed['client']}\\Attributes";
        }

        return $baseNamespace.'\\Attributes';
    }

    /**
     * Get request namespace from FQDN.
     */
    protected function getRequestNamespaceFromFqdn(string $classFqdn): string
    {
        $baseNamespace = $this->getBaseNamespaceFromFqdn($classFqdn);
        $parsed = $this->parseFqdn($classFqdn);

        if ($parsed['client']) {
            return "{$baseNamespace}\\{$parsed['client']}\\Requests";
        }

        return $baseNamespace.'\\Requests';
    }

    /**
     * Get response namespace from FQDN.
     */
    protected function getResponseNamespaceFromFqdn(string $classFqdn): string
    {
        $baseNamespace = $this->getBaseNamespaceFromFqdn($classFqdn);
        $parsed = $this->parseFqdn($classFqdn);

        if ($parsed['client']) {
            return "{$baseNamespace}\\{$parsed['client']}\\Responses";
        }

        return $baseNamespace.'\\Responses';
    }
}
