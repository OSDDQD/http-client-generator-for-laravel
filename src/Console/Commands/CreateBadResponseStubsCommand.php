<?php

namespace osddqd\HttpClientGenerator\Console\Commands;

use function Laravel\Prompts\text;

class CreateBadResponseStubsCommand extends AppCommand
{
    protected $signature = 'http-client-generator:bad-response
        {client?}
        {--namespace= : Custom base namespace}
        {--path= : Custom base path}
        {--tests-path= : Custom tests path}
        {--test-namespace= : Custom namespace for tests}
        {--no-tests : Skip test generation}';

    protected $description = 'Command for generating bad response classes with custom namespace and path support';

    public function handle()
    {
        $client = $this->argument('client') ?? text(
            label: 'What is the client\'s name?',
            placeholder: 'Trello, Twitter, Linkedin',
            hint: 'This will be the folder name e.g Http\Clients\Twitter\\',
            validate: ['clientName' => 'required|max:50'],
        );

        $this->createClassStub($client, 'Bad', 'Response');
        $this->createTestStub($client, 'Bad', 'Response');
    }
}
