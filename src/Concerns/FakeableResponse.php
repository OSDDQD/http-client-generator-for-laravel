<?php

namespace Jcergolj\HttpClientGenerator\Concerns;

use Illuminate\Http\Client\Response;

trait FakeableResponse
{
    public static function fake(): FakeableResponseBuilder
    {
        return new FakeableResponseBuilder(static::class);
    }
}

class FakeableResponseBuilder
{
    private string $responseClass;
    private Response $originalResponse;
    private int $status = 200;
    private array $data = [];

    public function __construct(string $responseClass)
    {
        $this->responseClass = $responseClass;
        $this->originalResponse = new Response(new \GuzzleHttp\Psr7\Response($this->status));
    }

    public function addOriginalResponse(?Response $response = null): self
    {
        $this->originalResponse = $response ?? new Response(new \GuzzleHttp\Psr7\Response($this->status));
        return $this;
    }

    public function addStatus(int $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function addData(array $data): self
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    public function build(): object
    {
        // Create a mock response with the specified data
        $mockResponse = $this->createMockResponse();

        // Use the fromResponse method to create the response instance
        return $this->responseClass::fromResponse($mockResponse);
    }

    private function createMockResponse(): Response
    {
        // Create a mock response with the specified status and data
        $mockPsrResponse = new \GuzzleHttp\Psr7\Response(
            $this->status,
            ['Content-Type' => 'application/json'],
            json_encode($this->data)
        );

        return new Response($mockPsrResponse);
    }
}
