<?php

namespace osddqd\HttpClientGenerator\Tests\Unit\Commands;

use osddqd\HttpClientGenerator\Console\Commands\CreateRequestStubsCommand;
use osddqd\HttpClientGenerator\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class RequestCommandTest extends TestCase
{
    #[Test]
    public function it_has_correct_signature()
    {
        $command = new CreateRequestStubsCommand;

        $this->assertStringContainsString('http-client-generator:request', $command->getName());
    }

    #[Test]
    public function it_generates_request_with_dependencies()
    {
        $this->artisan('http-client-generator:request', ['client' => 'TestClient', 'name' => 'TestRequest'])
            ->assertExitCode(0);
    }
}
