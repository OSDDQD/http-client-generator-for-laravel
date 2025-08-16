<?php

namespace Osddqd\HttpClientGenerator\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

use function Laravel\Prompts\text;

class CreateAllRequestStubsCommand extends Command
{
    protected $signature = 'http-client-generator:all {client?} {name?} {--no-tests : Skip test generation}';

    protected $description = 'Command for generating attributes, request, responses and factory';

    public function handle()
    {
        $clientName = $this->argument('client') ?? text(
            label: 'What is the client\'s name?',
            placeholder: 'Trello, Twitter, Linkedin',
            hint: 'This will be the folder name e.g Http\Clients\Twitter\\',
            validate: ['clientName' => 'required|max:50'],
        );

        $requestName = $this->argument('name') ?? text(
            label: 'What is name of the request?',
            placeholder: 'FetchOne, FetchAll, Create',
            hint: 'This will be the name e.g. FetchOneReqauest, FetchOneAttributes, FetchOneResponse',
            validate: ['requestName' => 'required|max:50'],
        );

        $noTestsOption = $this->option('no-tests') ? ' --no-tests' : '';

        Artisan::call("http-client-generator:attribute {$clientName} {$requestName}{$noTestsOption}");

        $output = Artisan::output();
        $this->info($output);

        Artisan::call("http-client-generator:request {$clientName} {$requestName}{$noTestsOption}");

        $output = Artisan::output();
        $this->info($output);

        Artisan::call("http-client-generator:response {$clientName} {$requestName}{$noTestsOption}");

        $output = Artisan::output();
        $this->info($output);

        Artisan::call("http-client-generator:bad-response {$clientName}{$noTestsOption}");

        $output = Artisan::output();
        $this->info($output);

        Artisan::call("http-client-generator:factory {$clientName} {$requestName}{$noTestsOption}");

        $output = Artisan::output();
        $this->info($output);
    }
}
