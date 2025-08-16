<?php

namespace osddqd\HttpClientGenerator\Console\Commands;

use function Laravel\Prompts\text;

class CreateAttributeStubsCommand extends AppCommand
{
    protected $signature = 'http-client-generator:attribute
        {client?}
        {name?}
        {--namespace= : Custom base namespace}
        {--path= : Custom base path}
        {--tests-path= : Custom tests path}
        {--test-namespace= : Custom namespace for tests}
        {--no-tests : Skip test generation}';

    protected $description = 'Command for generating request attributes with custom namespace and path support';

    public function handle()
    {
        $client = $this->argument('client') ?? text(
            label: 'What is the client\'s name?',
            placeholder: 'Trello, Twitter, Linkedin',
            hint: 'This will be the folder name e.g Http\Clients\Twitter\\',
            validate: ['clientName' => 'required|max:50'],
        );

        $name = $this->argument('name') ?? text(
            label: 'What is name of the attribute?',
            placeholder: 'FetchOne, FetchAll, Create',
            hint: 'This will be the name e.g. CreateAttribute, FetchOneAttribute',
            validate: ['requestName' => 'required|max:50'],
        );

        $this->createClassStub($client, $name, 'Attribute');
        $this->createTestStub($client, $name, 'Attribute');
    }
}
