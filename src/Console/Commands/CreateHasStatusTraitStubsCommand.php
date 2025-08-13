<?php

namespace Osddqd\HttpClientGenerator\Console\Commands;

use Illuminate\Console\Command;

class CreateHasStatusTraitStubsCommand extends Command
{
    protected $signature = 'http-client-generator:has-status-trait';

    protected $description = 'Command for generating hasStatus trait';

    public function handle()
    {
        $this->createHasTrait();

        $this->createHasTraitTest();
    }

    protected function createHasTrait()
    {
        $stub = file_get_contents(__DIR__.'../../../stubs/Clients/HasStatus.stub');

        if (file_exists(app_path('/Http/Clients/HasStatus.php'))) {
            $this->warn('HasTrait class already exists. Skipping.');

            return;
        }

        if (! is_dir(app_path('/Http/Clients/'))) {
            mkdir(app_path('/Http/Clients/'), 0755, true);
        }

        $file = fopen(app_path('/Http/Clients/HasStatus.php'), 'w');

        fwrite($file, $stub);
        fclose($file);

        $this->info('HasStatus class has been successfully created.');
    }

    protected function createHasTraitTest()
    {
        $stub = file_get_contents(__DIR__.'../../../stubs/Tests/HasStatus.stub');

        if (file_exists(app_path('../tests/Unit/Http/Clients/HasStatusTest.php'))) {
            $this->warn('HasTraitTest class already exists. Skipping.');

            return;
        }

        if (! is_dir(app_path('../tests/Unit/Http/Clients/'))) {
            mkdir(app_path('../tests/Unit/Http/Clients/'), 0755, true);
        }

        $file = fopen(app_path('../tests/Unit/Http/Clients/HasStatusTest.php'), 'w');

        fwrite($file, $stub);
        fclose($file);

        $this->info('HasStatusTest class has been successfully created.');
    }
}
