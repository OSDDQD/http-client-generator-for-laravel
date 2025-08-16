<?php

namespace osddqd\HttpClientGenerator\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

use function Laravel\Prompts\text;

class CreateAllRequestStubsCommand extends Command
{
    protected $signature = 'http-client-generator:all {client?} {name?} {--test-namespace= : Custom namespace for tests} {--no-tests : Skip test generation}';

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

        // Собираем опции для передачи в команды
        $options = [];
        if ($this->option('no-tests')) {
            $options['--no-tests'] = true;
        }
        if ($this->option('test-namespace')) {
            $options['--test-namespace'] = $this->option('test-namespace');
        }

        Artisan::call('http-client-generator:attribute', array_merge([
            'client' => $clientName,
            'name' => $requestName,
        ], $options));

        $output = Artisan::output();
        $this->info($output);

        Artisan::call('http-client-generator:request', array_merge([
            'client' => $clientName,
            'name' => $requestName,
        ], $options));

        $output = Artisan::output();
        $this->info($output);

        Artisan::call('http-client-generator:response', array_merge([
            'client' => $clientName,
            'name' => $requestName,
        ], $options));

        $output = Artisan::output();
        $this->info($output);

        Artisan::call('http-client-generator:bad-response', array_merge([
            'client' => $clientName,
        ], $options));

        $output = Artisan::output();
        $this->info($output);

        Artisan::call('http-client-generator:factory', array_merge([
            'client' => $clientName,
            'name' => $requestName,
        ], $options));

        $output = Artisan::output();
        $this->info($output);
    }
}
