<?php

namespace Osddqd\HttpClientGenerator\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

use function Laravel\Prompts\text;

class CreateClientMacroStubsCommand extends Command
{
    protected $signature = 'http-client-generator:client-macro {client?}';

    protected $description = 'Command for generating client macro file';

    public function handle()
    {
        $clientName = $this->argument('client') ?? text(
            label: 'What is the client\'s name?',
            placeholder: 'Trello, Twitter, Linkedin',
            hint: 'This will be the folder name e.g Http\Clients\Twitter\\',
            validate: ['clientName' => 'required|max:50'],
        );

        $this->createMacro($clientName);
        $this->createMacroTest($clientName);

        $this->info("✅ {$clientName}Macro has been created and will be automatically registered!");
        $this->line("🔄 The macro will be available after the next application restart or cache clear.");
    }

    protected function createMacro($client)
    {
        if (file_exists(app_path("/Http/Clients/{$client}/{$client}Macro.php"))) {
            $this->warn("{$client}Macro class already exists. Skipping.");

            return;
        }

        $file = file_get_contents(__DIR__ . '../../../stubs/Clients/Macro.stub');

        $namespace = "App\\Http\\Clients\\{$client}";
        $newStub = Str::of($file)->replace(['{{ namespace }}', '{{ client }}', '{{ method }}'], [$namespace, $client, strtolower($client)])->toString();

        if (! is_dir(app_path("/Http/Clients/{$client}"))) {
            mkdir(app_path("/Http/Clients/{$client}/"), 0755, true);
        }

        $file = fopen(app_path("/Http/Clients/{$client}/{$client}Macro.php"), 'w');

        fwrite($file, $newStub);
        fclose($file);

        $this->info("{$client}Macro class has been successfully created.");
    }

    protected function createMacroTest($client)
    {
        if (file_exists(app_path("../tests/Unit/Http/Clients/{$client}/{$client}Macro.php"))) {
            $this->warn("{$client}Macro class already exists. Skipping.");

            return;
        }

        $file = file_get_contents(__DIR__ . '../../../stubs/Tests/Macro.stub');

        $newStub = Str::of($file)->replace(['{{ client }}', '{{ method }}'], [$client, strtolower($client)])->toString();

        if (! is_dir(app_path("../tests/Unit/Http/Clients/{$client}"))) {
            mkdir(app_path("../tests/Unit/Http/Clients/{$client}"), 0755, true);
        }

        $file = fopen(app_path("../tests/Unit/Http/Clients/{$client}/{$client}Macro.php"), 'w');

        fwrite($file, $newStub);
        fclose($file);

        $this->info("{$client}MacroTest class has been successfully created.");
    }
}
