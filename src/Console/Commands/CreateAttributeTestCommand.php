<?php

namespace osddqd\HttpClientGenerator\Console\Commands;

use function Laravel\Prompts\text;

class CreateAttributeTestCommand extends AppCommand
{
    protected $signature = 'http-client-generator:test:attribute
        {class_fqdn? : FQDN of the class to test}
        {--test-namespace= : Custom namespace for tests}';

    protected $description = 'Command for generating attribute test classes using FQDN';

    public function handle()
    {
        $classFqdn = $this->argument('class_fqdn') ?? text(
            label: 'What is the FQDN of the attribute class to test?',
            placeholder: 'App\\Http\\Clients\\GitHub\\Attributes\\GetUserAttribute',
            hint: 'Full qualified class name including namespace',
            validate: ['classFqdn' => 'required|max:255'],
        );

        // Проверяем, что класс существует
        if (! class_exists($classFqdn)) {
            $this->error("Class {$classFqdn} does not exist.");
            $this->info('Please create the class first using the appropriate generator command.');

            return 1;
        }

        // Проверяем, что это действительно класс атрибута
        if (! str_ends_with($classFqdn, 'Attribute')) {
            $this->error("Class {$classFqdn} does not appear to be an Attribute class.");
            $this->info("Attribute classes should end with 'Attribute'.");

            return 1;
        }

        $this->createTestStubFromFqdn($classFqdn, 'Attribute');

        return 0;
    }

    /**
     * Test commands should always generate tests.
     */
    protected function shouldGenerateTests(): bool
    {
        return true;
    }
}
