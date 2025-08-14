<?php

namespace Osddqd\HttpClientGenerator\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ListMacrosCommand extends Command
{
    protected $signature = 'http-client-generator:list-macros';

    protected $description = 'List all registered HTTP client macros';

    public function handle()
    {
        $baseNamespace = config('http-client-generator.namespace.base', 'App\\Http\\Clients');
        $basePath = config('http-client-generator.paths.base', 'app/Http/Clients');
        
        // Получаем абсолютный путь к директории с клиентами
        $clientsPath = base_path($basePath);
        
        if (! is_dir($clientsPath)) {
            $this->warn("Clients directory not found: {$clientsPath}");
            return;
        }
        
        $this->info('🔍 Discovering HTTP Client Macros...');
        $this->newLine();
        
        // Сканируем директории клиентов
        $clientDirectories = glob($clientsPath . '/*', GLOB_ONLYDIR);
        $foundMacros = [];
        
        foreach ($clientDirectories as $clientDir) {
            $clientName = basename($clientDir);
            $macroFile = $clientDir . '/' . $clientName . 'Macro.php';
            
            if (file_exists($macroFile)) {
                $macroClass = $baseNamespace . '\\' . $clientName . '\\' . $clientName . 'Macro';
                
                $status = class_exists($macroClass) ? '✅ Loaded' : '❌ Not Found';
                $methodName = strtolower($clientName);
                $isRegistered = Http::hasMacro($methodName) ? '🟢 Registered' : '🔴 Not Registered';
                
                $foundMacros[] = [
                    'Client' => $clientName,
                    'Method' => $methodName,
                    'Class' => $macroClass,
                    'Status' => $status,
                    'Registered' => $isRegistered,
                ];
            }
        }
        
        if (empty($foundMacros)) {
            $this->warn('No HTTP client macros found.');
            $this->line("💡 Create a macro with: php artisan http-client-generator:macro");
            return;
        }
        
        $this->table(
            ['Client', 'Method', 'Class', 'Status', 'Registered'],
            $foundMacros
        );
        
        $this->newLine();
        $this->info('📋 Summary:');
        $this->line("• Total macros found: " . count($foundMacros));
        $this->line("• Auto-registration: " . (config('http-client-generator.auto_register.enabled', true) ? 'Enabled' : 'Disabled'));
        
        if (! config('http-client-generator.auto_register.enabled', true)) {
            $this->newLine();
            $this->warn('⚠️  Auto-registration is disabled. Enable it in config or register macros manually.');
        }
    }
}
