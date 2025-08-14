<?php

namespace Osddqd\HttpClientGenerator\Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Osddqd\HttpClientGenerator\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class AutoMacroRegistrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Очищаем кэш перед каждым тестом
        Cache::forget('http_client_generator.macros');
    }

    #[Test]
    public function it_can_discover_and_register_macros_automatically()
    {
        // Создаем тестовую структуру директорий
        $this->createTestMacroStructure();
        
        // Перезагружаем service provider для тестирования автоматической регистрации
        $this->app->register(\Osddqd\HttpClientGenerator\HttpClientGeneratorServiceProvider::class);
        
        // Проверяем, что макрос зарегистрирован
        $this->assertTrue(Http::hasMacro('testclient'));
    }

    #[Test]
    public function it_respects_auto_register_configuration()
    {
        // Отключаем автоматическую регистрацию
        Config::set('http-client-generator.auto_register.enabled', false);
        
        $this->createTestMacroStructure();
        
        // Перезагружаем service provider
        $this->app->register(\Osddqd\HttpClientGenerator\HttpClientGeneratorServiceProvider::class);
        
        // Проверяем, что макрос НЕ зарегистрирован
        $this->assertFalse(Http::hasMacro('testclient'));
    }

    #[Test]
    public function it_caches_discovered_macros()
    {
        $this->createTestMacroStructure();
        
        // Первый вызов должен создать кэш
        $this->app->register(\Osddqd\HttpClientGenerator\HttpClientGeneratorServiceProvider::class);
        
        // Проверяем, что кэш создан
        $this->assertTrue(Cache::has('http_client_generator.macros'));
        
        // Проверяем содержимое кэша
        $cachedMacros = Cache::get('http_client_generator.macros');
        $this->assertIsArray($cachedMacros);
        $this->assertContains('App\\Http\\Clients\\TestClient\\TestClientMacro', $cachedMacros);
    }

    #[Test]
    public function clear_cache_command_works()
    {
        // Создаем кэш
        Cache::put('http_client_generator.macros', ['test'], 3600);
        
        // Выполняем команду очистки кэша
        $this->artisan('http-client-generator:clear-cache')
            ->expectsOutput('✅ HTTP client macros cache has been cleared!')
            ->assertExitCode(0);
        
        // Проверяем, что кэш очищен
        $this->assertFalse(Cache::has('http_client_generator.macros'));
    }

    #[Test]
    public function list_macros_command_shows_discovered_macros()
    {
        $this->createTestMacroStructure();
        
        $this->artisan('http-client-generator:list-macros')
            ->expectsOutput('🔍 Discovering HTTP Client Macros...')
            ->assertExitCode(0);
    }

    protected function createTestMacroStructure()
    {
        $clientsPath = app_path('Http/Clients/TestClient');
        
        if (!is_dir($clientsPath)) {
            mkdir($clientsPath, 0755, true);
        }
        
        $macroContent = '<?php

namespace App\Http\Clients\TestClient;

use Illuminate\Support\Facades\Http;

class TestClientMacro
{
    public function testclient(): callable
    {
        return function () {
            return Http::withHeaders([
                "accept" => "application/json",
                "content-type" => "application/json",
            ])->baseUrl("https://api.test.com");
        };
    }
}';
        
        file_put_contents($clientsPath . '/TestClientMacro.php', $macroContent);
    }

    protected function tearDown(): void
    {
        // Очищаем тестовые файлы
        $testPath = app_path('Http/Clients/TestClient');
        if (is_dir($testPath)) {
            array_map('unlink', glob($testPath . '/*'));
            rmdir($testPath);
        }
        
        Cache::forget('http_client_generator.macros');
        
        parent::tearDown();
    }
}
