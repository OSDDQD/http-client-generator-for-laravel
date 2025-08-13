<?php

namespace Osddqd\HttpClientGenerator\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class AppCommand extends Command
{
    /**
     * Get the namespace for the given client and type.
     */
    protected function getNamespace(string $client, string $type): string
    {
        $baseNamespace = $this->option('namespace') ?? config('http-client-generator.namespace.base', 'App\\Http\\Clients');
        $typeNamespace = config("http-client-generator.namespace." . strtolower($type) . "s", ucfirst($type) . 's');
        
        return "{$baseNamespace}\\{$client}\\{$typeNamespace}";
    }
    
    /**
     * Get the file path for the class.
     */
    protected function getClassPath(string $client, string $name, string $type): string
    {
        $basePath = $this->option('path') ?? config('http-client-generator.paths.base', 'app/Http/Clients');
        $typePath = ucfirst($type) . 's';
        
        return base_path("{$basePath}/{$client}/{$typePath}/{$name}{$type}.php");
    }
    
    /**
     * Get the file path for the test.
     */
    protected function getTestPath(string $client, string $name, string $type): string
    {
        $testsPath = $this->option('tests-path') ?? config('http-client-generator.paths.tests', 'tests/Unit/Http/Clients');
        $typePath = ucfirst($type) . 's';
        
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
        
        return __DIR__ . '/../../stubs/Clients/' . $type . '.stub';
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
        
        return __DIR__ . '/../../stubs/Tests/' . $type . '.stub';
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

        $namespace = $this->getNamespace($client, $type);
        $stubPath = $this->getStubPath($type);
        
        if (!file_exists($stubPath)) {
            $this->error("Stub file not found: {$stubPath}");
            return;
        }
        
        $file = file_get_contents($stubPath);
        $newStub = Str::of($file)
            ->replace(['{{ namespace }}', '{{ client }}', '{{ name }}'], [$namespace, $client, $name])
            ->toString();

        $directory = dirname($classPath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents($classPath, $newStub);
        $this->info("{$type} class has been successfully created at: {$classPath}");
    }

    /**
     * Create a test stub file.
     */
    protected function createTestStub($client, $name, $type)
    {
        $testPath = $this->getTestPath($client, $name, $type);
        
        if (file_exists($testPath)) {
            $this->warn("{$name}{$type}Test class already exists. Skipping.");
            return;
        }

        $namespace = $this->getNamespace($client, $type);
        $stubPath = $this->getTestStubPath($type);
        
        if (!file_exists($stubPath)) {
            $this->error("Test stub file not found: {$stubPath}");
            return;
        }
        
        $stub = file_get_contents($stubPath);
        $newStub = Str::of($stub)
            ->replace(['{{ namespace }}', '{{ client }}', '{{ name }}'], [$namespace, $client, $name])
            ->toString();

        $directory = dirname($testPath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents($testPath, $newStub);
        $this->info("{$type}Test class has been successfully created at: {$testPath}");
    }
}
