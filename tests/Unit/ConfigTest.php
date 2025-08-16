<?php

namespace Osddqd\HttpClientGenerator\Tests\Unit;

use Illuminate\Support\Facades\Config;
use Osddqd\HttpClientGenerator\Tests\TestCase;
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
}
