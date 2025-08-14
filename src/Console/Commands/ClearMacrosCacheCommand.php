<?php

namespace Osddqd\HttpClientGenerator\Console\Commands;

use Illuminate\Console\Command;

class ClearMacrosCacheCommand extends Command
{
    protected $signature = 'http-client-generator:clear-cache';

    protected $description = 'Clear the HTTP client macros cache';

    public function handle()
    {
        cache()->forget('http_client_generator.macros');
        
        $this->info('✅ HTTP client macros cache has been cleared!');
        $this->line('🔄 Macros will be re-discovered on the next request.');
    }
}
