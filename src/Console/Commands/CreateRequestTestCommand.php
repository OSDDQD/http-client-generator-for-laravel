<?php

namespace osddqd\HttpClientGenerator\Console\Commands;

use function Laravel\Prompts\text;

class CreateRequestTestCommand extends AppCommand
{
    protected $signature = 'http-client-generator:test:request
        {class_fqdn? : FQDN of the class to test}
        {--test-namespace= : Custom namespace for tests}';

    protected $description = 'Command for generating request test classes using FQDN';

    public function handle()
    {
        $classFqdn = $this->argument('class_fqdn') ?? text(
            label: 'What is the FQDN of the request class to test?',
            placeholder: 'App\\Http\\Clients\\GitHub\\Requests\\GetUserRequest',
            hint: 'Full qualified class name including namespace',
            validate: ['classFqdn' => 'required|max:255'],
        );

        // Проверяем, что класс существует
        if (! class_exists($classFqdn)) {
            $this->error("Class {$classFqdn} does not exist.");
            $this->info('Please create the class first using the appropriate generator command.');

            return 1;
        }

        // Проверяем, что это действительно класс запроса
        if (! str_ends_with($classFqdn, 'Request')) {
            $this->error("Class {$classFqdn} does not appear to be a Request class.");
            $this->info("Request classes should end with 'Request'.");

            return 1;
        }

        $this->createTestStubFromFqdn($classFqdn, 'Request');

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
