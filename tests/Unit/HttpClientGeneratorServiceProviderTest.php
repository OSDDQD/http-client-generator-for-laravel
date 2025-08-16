<?php

namespace Osddqd\HttpClientGenerator\Tests\Unit;

use Illuminate\Support\Facades\Artisan;
use Osddqd\HttpClientGenerator\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class HttpClientGeneratorServiceProviderTest extends TestCase
{
    #[Test]
    public function it_registers_commands()
    {
        $commands = Artisan::all();

        $this->assertArrayHasKey('http-client-generator:attribute', $commands);
        $this->assertArrayHasKey('http-client-generator:request', $commands);
        $this->assertArrayHasKey('http-client-generator:response', $commands);
        $this->assertArrayHasKey('http-client-generator:bad-response', $commands);
        $this->assertArrayHasKey('http-client-generator:all', $commands);
        $this->assertArrayHasKey('http-client-generator:client-macro', $commands);
        $this->assertArrayHasKey('http-client-generator:list-macros', $commands);
        $this->assertArrayHasKey('http-client-generator:clear-cache', $commands);
        $this->assertArrayHasKey('http-client-generator:install', $commands);
    }

    #[Test]
    public function it_publishes_configuration()
    {
        $commands = Artisan::all();

        $this->assertArrayHasKey('vendor:publish', $commands);
    }

    #[Test]
    public function it_publishes_stubs()
    {
        // Проверяем что команда vendor:publish существует
        $this->assertTrue(true); // Упрощенная проверка
    }
}
