<?php

namespace Osddqd\HttpClientGenerator\Tests\Unit\Commands;

use Osddqd\HttpClientGenerator\Console\Commands\CreateAttributeStubsCommand;
use Osddqd\HttpClientGenerator\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class AttributeCommandTest extends TestCase
{
    #[Test]
    public function it_has_correct_signature()
    {
        $command = new CreateAttributeStubsCommand();
        
        $this->assertStringContainsString('http-client-generator:attribute', $command->getName());
    }

    #[Test]
    public function it_validates_required_arguments()
    {
        $this->artisan('http-client-generator:attribute', ['client' => 'TestClient', 'name' => 'TestAttribute'])
            ->assertExitCode(0);
    }
}
