#!/bin/bash

# Update CreateRequestStubsCommand
cat > src/Console/Commands/CreateRequestStubsCommand.php << 'COMMAND_EOF'
<?php

namespace Osddqd\HttpClientGenerator\Console\Commands;

use function Laravel\Prompts\text;

class CreateRequestStubsCommand extends AppCommand
{
    protected $signature = 'http-client-generator:request 
        {client?} 
        {name?}
        {--namespace= : Custom base namespace}
        {--path= : Custom base path}
        {--tests-path= : Custom tests path}';

    protected $description = 'Command for generating request classes with custom namespace and path support';

    public function handle()
    {
        $client = $this->argument('client') ?? text(
            label: 'What is the client\'s name?',
            placeholder: 'Trello, Twitter, Linkedin',
            hint: 'This will be the folder name e.g Http\Clients\Twitter\\',
            validate: ['clientName' => 'required|max:50']
        );

        $name = $this->argument('name') ?? text(
            label: 'What is name of the request?',
            placeholder: 'FetchOne, FetchAll, Create',
            hint: 'This will be the name e.g. CreateRequest, FetchOneRequest',
            validate: ['requestName' => 'required|max:50']
        );

        $this->createClassStub($client, $name, 'Request');
        $this->createTestStub($client, $name, 'Request');
    }
}
COMMAND_EOF

# Update CreateResponseStubsCommand
cat > src/Console/Commands/CreateResponseStubsCommand.php << 'COMMAND_EOF'
<?php

namespace Osddqd\HttpClientGenerator\Console\Commands;

use function Laravel\Prompts\text;

class CreateResponseStubsCommand extends AppCommand
{
    protected $signature = 'http-client-generator:response 
        {client?} 
        {name?}
        {--namespace= : Custom base namespace}
        {--path= : Custom base path}
        {--tests-path= : Custom tests path}';

    protected $description = 'Command for generating response classes with custom namespace and path support';

    public function handle()
    {
        $client = $this->argument('client') ?? text(
            label: 'What is the client\'s name?',
            placeholder: 'Trello, Twitter, Linkedin',
            hint: 'This will be the folder name e.g Http\Clients\Twitter\\',
            validate: ['clientName' => 'required|max:50']
        );

        $name = $this->argument('name') ?? text(
            label: 'What is name of the response?',
            placeholder: 'FetchOne, FetchAll, Create',
            hint: 'This will be the name e.g. CreateResponse, FetchOneResponse',
            validate: ['responseName' => 'required|max:50']
        );

        $this->createClassStub($client, $name, 'Response');
        $this->createTestStub($client, $name, 'Response');
    }
}
COMMAND_EOF

# Update CreateBadResponseStubsCommand
cat > src/Console/Commands/CreateBadResponseStubsCommand.php << 'COMMAND_EOF'
<?php

namespace Osddqd\HttpClientGenerator\Console\Commands;

use function Laravel\Prompts\text;

class CreateBadResponseStubsCommand extends AppCommand
{
    protected $signature = 'http-client-generator:bad-response 
        {client?}
        {--namespace= : Custom base namespace}
        {--path= : Custom base path}
        {--tests-path= : Custom tests path}';

    protected $description = 'Command for generating bad response classes with custom namespace and path support';

    public function handle()
    {
        $client = $this->argument('client') ?? text(
            label: 'What is the client\'s name?',
            placeholder: 'Trello, Twitter, Linkedin',
            hint: 'This will be the folder name e.g Http\Clients\Twitter\\',
            validate: ['clientName' => 'required|max:50']
        );

        $this->createClassStub($client, 'Bad', 'Response');
        $this->createTestStub($client, 'Bad', 'Response');
    }
}
COMMAND_EOF

echo "Commands updated successfully!"
