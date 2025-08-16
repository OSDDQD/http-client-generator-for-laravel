<?php

namespace osddqd\HttpClientGenerator\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

use function Laravel\Prompts\text;

class CreateAllTestsCommand extends Command
{
    protected $signature = 'http-client-generator:test:all
        {base_namespace?}
        {name?}
        {--test-namespace= : Custom namespace for tests}';

    protected $description = 'Command for generating all test classes (attribute, request, response, factory, bad-response) for existing HTTP client classes using base namespace';

    public function handle()
    {
        $baseNamespace = $this->argument('base_namespace') ?? text(
            label: 'What is the base namespace for the client classes?',
            placeholder: 'App\\Http\\Clients\\GitHub',
            hint: 'Base namespace where all client classes are located',
            validate: ['baseNamespace' => 'required|max:255'],
        );

        $name = $this->argument('name') ?? text(
            label: 'What is the name of the classes?',
            placeholder: 'GetUser',
            hint: 'Name used in class names (e.g. GetUserAttribute, GetUserRequest)',
            validate: ['name' => 'required|max:50'],
        );

        // Собираем опции для передачи в команды
        $options = [];
        if ($this->option('test-namespace')) {
            $options['--test-namespace'] = $this->option('test-namespace');
        }

        $this->info("Creating tests for {$baseNamespace} {$name}...");

        // Формируем FQDN для каждого типа класса
        $classFqdns = [
            'attribute' => "{$baseNamespace}\\Attributes\\{$name}Attribute",
            'request' => "{$baseNamespace}\\Requests\\{$name}Request",
            'response' => "{$baseNamespace}\\Responses\\{$name}Response",
            'factory' => "{$baseNamespace}\\Factories\\{$name}Factory",
            'bad-response' => "{$baseNamespace}\\Responses\\BadResponse",
        ];

        // Создаем тест для атрибута
        if (class_exists($classFqdns['attribute'])) {
            $this->info('Creating attribute test...');
            Artisan::call('http-client-generator:test:attribute', array_merge([
                'class_fqdn' => $classFqdns['attribute'],
            ], $options));
            $this->line(Artisan::output());
        } else {
            $this->warn("Attribute class {$classFqdns['attribute']} does not exist. Skipping.");
        }

        // Создаем тест для запроса
        if (class_exists($classFqdns['request'])) {
            $this->info('Creating request test...');
            Artisan::call('http-client-generator:test:request', array_merge([
                'class_fqdn' => $classFqdns['request'],
            ], $options));
            $this->line(Artisan::output());
        } else {
            $this->warn("Request class {$classFqdns['request']} does not exist. Skipping.");
        }

        // Создаем тест для ответа
        if (class_exists($classFqdns['response'])) {
            $this->info('Creating response test...');
            Artisan::call('http-client-generator:test:response', array_merge([
                'class_fqdn' => $classFqdns['response'],
            ], $options));
            $this->line(Artisan::output());
        } else {
            $this->warn("Response class {$classFqdns['response']} does not exist. Skipping.");
        }

        // Создаем тест для фабрики
        if (class_exists($classFqdns['factory'])) {
            $this->info('Creating factory test...');
            Artisan::call('http-client-generator:test:factory', array_merge([
                'class_fqdn' => $classFqdns['factory'],
            ], $options));
            $this->line(Artisan::output());
        } else {
            $this->warn("Factory class {$classFqdns['factory']} does not exist. Skipping.");
        }

        // Создаем тест для BadResponse
        if (class_exists($classFqdns['bad-response'])) {
            $this->info('Creating bad response test...');
            Artisan::call('http-client-generator:test:bad-response', array_merge([
                'class_fqdn' => $classFqdns['bad-response'],
            ], $options));
            $this->line(Artisan::output());
        } else {
            $this->warn("BadResponse class {$classFqdns['bad-response']} does not exist. Skipping.");
        }

        $this->info('All available tests created successfully!');

        return 0;
    }
}
