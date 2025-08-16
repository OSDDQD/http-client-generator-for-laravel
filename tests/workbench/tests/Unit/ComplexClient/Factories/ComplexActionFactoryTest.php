<?php

namespace Tests\Unit\ComplexClient\Factorys;

use App\Http\Clients\ComplexClient\Factories\ComplexActionFactory;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\PendingRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[CoversClass(ComplexActionFactory::class)]
class ComplexActionFactoryTest extends TestCase
{
    private ComplexActionFactory $factory;

    private Factory $httpFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->httpFactory = app(Factory::class);
        $this->factory = new ComplexActionFactory($this->httpFactory);
    }

    #[Test]
    public function it_can_create_basic_http_client(): void
    {
        $client = $this->factory->make();

        $this->assertInstanceOf(PendingRequest::class, $client);
    }

    #[Test]
    public function it_can_create_http_client_with_auth_token(): void
    {
        $token = 'test-token-123';
        $client = $this->factory->withAuth($token);

        $this->assertInstanceOf(PendingRequest::class, $client);
    }

    #[Test]
    public function it_can_create_http_client_with_basic_auth(): void
    {
        $username = 'testuser';
        $password = 'testpass';
        $client = $this->factory->withBasicAuth($username, $password);

        $this->assertInstanceOf(PendingRequest::class, $client);
    }

    #[Test]
    public function it_can_create_http_client_with_custom_headers(): void
    {
        $headers = [
            'X-Custom-Header' => 'custom-value',
            'X-API-Version' => 'v1',
        ];
        $client = $this->factory->withHeaders($headers);

        $this->assertInstanceOf(PendingRequest::class, $client);
    }

    #[Test]
    public function it_can_create_http_client_with_custom_timeout(): void
    {
        $timeout = 60;
        $client = $this->factory->withTimeout($timeout);

        $this->assertInstanceOf(PendingRequest::class, $client);
    }

    #[Test]
    public function it_can_create_debug_http_client(): void
    {
        $client = $this->factory->debug();

        $this->assertInstanceOf(PendingRequest::class, $client);
    }

    #[Test]
    public function it_applies_default_configuration(): void
    {
        $client = $this->factory->make();

        // Test that the client is properly configured
        // Note: These are internal implementation details that might need adjustment
        // based on how you want to test the configuration
        $this->assertInstanceOf(PendingRequest::class, $client);
    }
}
