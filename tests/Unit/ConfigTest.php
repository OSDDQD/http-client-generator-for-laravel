<?php

namespace osddqd\HttpClientGenerator\Tests\Unit;

use Illuminate\Support\Facades\Config;
use osddqd\HttpClientGenerator\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ConfigTest extends TestCase
{
    #[Test]
    public function it_has_default_configuration()
    {
        $config = Config::get('http-client-generator');

        $this->assertIsArray($config);
        $this->assertArrayHasKey('paths', $config);
        $this->assertArrayHasKey('namespace', $config);
        $this->assertArrayHasKey('stubs', $config);
    }

    #[Test]
    public function it_has_correct_default_paths()
    {
        $paths = Config::get('http-client-generator.paths');

        $this->assertArrayHasKey('base', $paths);
        $this->assertArrayHasKey('tests', $paths);
        $this->assertEquals('app/Http/Clients', $paths['base']);
        $this->assertEquals('tests/Unit', $paths['tests']);
    }

    #[Test]
    public function it_has_correct_default_namespaces()
    {
        $namespaces = Config::get('http-client-generator.namespace');

        $this->assertArrayHasKey('base', $namespaces);
        $this->assertArrayHasKey('tests', $namespaces);
        $this->assertEquals('App\\Http\\Clients', $namespaces['base']);
        $this->assertEquals('Tests\\Unit', $namespaces['tests']);
    }

    #[Test]
    public function it_can_override_configuration()
    {
        Config::set('http-client-generator.paths.base', 'custom/path');

        $path = Config::get('http-client-generator.paths.base');
        $this->assertEquals('custom/path', $path);
    }

    #[Test]
    public function it_has_stub_configuration()
    {
        $stubs = Config::get('http-client-generator.stubs');

        $this->assertIsArray($stubs);
        $this->assertArrayHasKey('path', $stubs);
    }

    #[Test]
    public function it_has_test_generation_configuration()
    {
        $generateTests = Config::get('http-client-generator.generate_tests');

        $this->assertTrue((bool) $generateTests); // По умолчанию должно быть true
    }

    #[Test]
    public function it_can_disable_test_generation_globally()
    {
        Config::set('http-client-generator.generate_tests', false);

        $generateTests = Config::get('http-client-generator.generate_tests');
        $this->assertFalse($generateTests);
    }

    #[Test]
    public function it_merges_partial_configuration_correctly()
    {
        // Симулируем частичную конфигурацию пользователя
        Config::set('http-client-generator.namespace.base', 'App\\External\\Clients');

        // Проверяем, что переопределенное значение применилось
        $this->assertEquals('App\\External\\Clients', Config::get('http-client-generator.namespace.base'));

        // Проверяем, что остальные значения остались по умолчанию
        $this->assertEquals('Attributes', Config::get('http-client-generator.namespace.attributes'));
        $this->assertEquals('Requests', Config::get('http-client-generator.namespace.requests'));
        $this->assertEquals('Factories', Config::get('http-client-generator.namespace.factories'));
        $this->assertEquals('app/Http/Clients', Config::get('http-client-generator.paths.base'));
    }
}
