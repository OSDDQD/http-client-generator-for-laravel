<?php

namespace Osddqd\HttpClientGenerator\Console\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'http-client-generator:install';
    
    protected $description = 'Install the HTTP Client Generator configuration file';

    public function handle()
    {
        $this->info('Publishing HTTP Client Generator configuration...');
        
        $this->call('vendor:publish', [
            '--provider' => 'Osddqd\\HttpClientGenerator\\HttpClientGeneratorServiceProvider',
            '--tag' => 'config',
            '--force' => $this->option('force', false)
        ]);
        
        $this->info('HTTP Client Generator configuration published successfully!');
        $this->line('');
        $this->line('You can now customize the configuration in: <comment>config/http-client-generator.php</comment>');
        $this->line('');
        $this->line('Available commands:');
        $this->line('  <comment>php artisan http-client-generator:attribute</comment>');
        $this->line('  <comment>php artisan http-client-generator:request</comment>');
        $this->line('  <comment>php artisan http-client-generator:response</comment>');
        $this->line('  <comment>php artisan http-client-generator:bad-response</comment>');
        $this->line('');
        $this->line('Use <comment>--namespace</comment>, <comment>--path</comment>, and <comment>--tests-path</comment> options to override defaults.');
    }
}
