<?php

namespace Osddqd\HttpClientGenerator\Concerns;

use Illuminate\Http\Client\Factory;

trait FakeableRequest
{
    private static ?self $fake = null;
    private mixed $fakeResponse = null;

    public static function fake(): self
    {
        if (self::$fake === null) {
            $reflection = new \ReflectionClass(static::class);
            if ($reflection->isAbstract()) {
                throw new \RuntimeException('Cannot fake abstract class: ' . static::class);
            }
            self::$fake = new static(new Factory());
        }

        return self::$fake;
    }

    public function respondWith(mixed $response): self
    {
        $this->fakeResponse = $response;
        return $this;
    }

    public function respondSuccess(mixed $response): self
    {
        return $this->respondWith($response);
    }

    public function respondError(mixed $response): self
    {
        return $this->respondWith($response);
    }

    protected function getFakeResponse(): mixed
    {
        if ($this->fakeResponse === null) {
            throw new \RuntimeException('No fake response set. Call respondSuccess() or respondError() first.');
        }
        return $this->fakeResponse;
    }

    protected function hasFakeResponse(): bool
    {
        return $this->fakeResponse !== null;
    }

    /**
     * Override this method in your request class to provide a default success response.
     */
    protected function getDefaultSuccessResponse(): mixed
    {
        return null;
    }

    /**
     * Override this method in your request class to provide a default error response.
     */
    protected function getDefaultErrorResponse(): mixed
    {
        return null;
    }
}
