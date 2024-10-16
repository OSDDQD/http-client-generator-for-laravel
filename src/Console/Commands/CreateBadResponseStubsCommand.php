<?php

namespace Jcergolj\HttpClientGenerator\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

use function Laravel\Prompts\text;

class CreateBadResponseStubsCommand extends Command
{
    protected $signature = 'http-client-generator:bad-response {client?}';

    protected $description = 'Command for generating bad response';

    public function handle()
    {
        $clientName = $this->argument('client') ?? text(
            label: 'What is the client\'s name?',
            placeholder: 'Trello, Twitter, Linkedin',
            hint: 'This will be the folder name e.g Http\Clients\Twitter\\',
            validate: ['clientName' => 'required|max:50']
        );

        $this->createBadResponse($clientName);

        $this->createBadResponseTest($clientName);
    }

    protected function createBadResponse($client)
    {
        if (file_exists(app_path("/Http/Clients/{$client}/BadResponse.php"))) {
            $this->warn('BadResponse class already exists. Skipping.');

            return;
        }

        $file = file_get_contents(__DIR__.'../../../stubs/Clients/BadResponse.stub');

        $newStub = Str::of($file)->replace(['{{ client }}'], [$client])->toString();

        if (! is_dir(app_path("/Http/Clients/{$client}/Responses"))) {
            mkdir(app_path("/Http/Clients/{$client}/Responses"), 0755, true);
        }

        $file = fopen(app_path("/Http/Clients/{$client}/Responses/BadResponse.php"), 'w');

        fwrite($file, $newStub);
        fclose($file);

        $this->info('BadResponse class has been successfully created.');
    }

    protected function createBadResponseTest($client)
    {
        if (file_exists(app_path("../tests/Unit/Http/Clients/{$client}/Responses/BadResponse.php"))) {
            $this->warn('BadResponseTest class already exists. Skipping.');

            return;
        }

        $file = file_get_contents(__DIR__.'../../../stubs/Tests/BadResponse.stub');

        $newStub = Str::of($file)->replace(['{{ client }}'], [$client])->toString();

        if (! is_dir(app_path("../tests/Unit/Http/Clients/{$client}/Responses"))) {
            mkdir(app_path("../tests/Unit/Http/Clients/{$client}/Responses"), 0755, true);
        }

        $file = fopen(app_path("../tests/Unit/Http/Clients/{$client}/Responses/BadResponseTest.php"), 'w');

        fwrite($file, $newStub);
        fclose($file);

        $this->info('BadResponseTest class has been successfully created.');
    }
}
