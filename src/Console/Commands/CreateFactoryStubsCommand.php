<?php

namespace osddqd\HttpClientGenerator\Console\Commands;

use function Laravel\Prompts\text;

class CreateFactoryStubsCommand extends AppCommand
{
    protected $signature = 'http-client-generator:factory
        {client?}
        {name?}
        {--namespace= : Custom base namespace}
        {--path= : Custom base path}
        {--tests-path= : Custom tests path}
        {--test-namespace= : Custom namespace for tests}
        {--no-tests : Skip test generation}';

    protected $description = 'Command for generating HTTP client factory classes with custom namespace and path support';

    public function handle()
    {
        $client = $this->argument('client') ?? text(
            label: 'What is the client\'s name?',
            placeholder: 'Trello, Twitter, Linkedin',
            hint: 'This will be the folder name e.g Http\Clients\Twitter\\',
            validate: ['clientName' => 'required|max:50'],
        );

        $name = $this->argument('name') ?? text(
            label: 'What is name of the factory?',
            placeholder: 'Api, Client, Http',
            hint: 'This will be the name e.g. ApiFactory, ClientFactory',
            validate: ['factoryName' => 'required|max:50'],
        );

        $this->createClassStub($client, $name, 'Factory');
        $this->createTestStub($client, $name, 'Factory');
    }
}
