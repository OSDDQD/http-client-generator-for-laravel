<?php

namespace Jcergolj\HttpClientGenerator\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class AppCommand extends Command
{
    protected function createClassStub($client, $name, $type)
    {
        if (file_exists(app_path("/Http/Clients/{$client}/{$type}s/{$name}{$type}.php"))) {
            $this->warn("{$name}{$type} class already exists. Skipping.");

            return;
        }

        $file = file_get_contents(__DIR__.'../../../stubs/Clients/'.$type.'.stub');

        $newStub = Str::of($file)->replace(['{{ client }}', '{{ name }}'], [$client, $name])->toString();

        if (! is_dir(app_path("/Http/Clients/{$client}/{$type}s"))) {
            mkdir(app_path("/Http/Clients/{$client}/{$type}s"), 0755, true);
        }

        $file = fopen(app_path("/Http/Clients/{$client}/{$type}s/{$name}{$type}.php"), 'w');

        fwrite($file, $newStub);
        fclose($file);

        $this->info("{$type} class has been successfully created.");
    }

    protected function createTestStub($client, $name, $type)
    {
        if (file_exists(app_path("../tests/Unit/Http/Clients/{$client}/{$type}s/{$name}{$type}Test.php"))) {
            $this->warn("{$name}{$type}Test class already exists. Skipping.");

            return;
        }

        $stub = file_get_contents(__DIR__.'../../../stubs/Tests/'.$type.'.stub');
        $newStub = Str::of($stub)->replace(['{{ client }}', '{{ name }}'], [$client, $name])->toString();

        if (! is_dir(app_path("../tests/Unit/Http/Clients/{$client}/{$type}s"))) {
            mkdir(app_path("../tests/Unit/Http/Clients/{$client}/{$type}s"), 0755, true);
        }

        $file = fopen(app_path("../tests/Unit/Http/Clients/{$client}/{$type}s/{$name}{$type}Test.php"), 'w');

        fwrite($file, $newStub);
        fclose($file);

        $this->info("{$type}Test class has been successfully created.");
    }
}
