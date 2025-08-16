<?php

namespace App\Http\Clients\Stripe\Responses;

use Illuminate\Http\Client\Response;

class CreateChargeResponse
{
    private function __construct(public Response $original, public int $status /* public int $id */) {}

    public static function fromResponse(Response $response): self
    {
        return new self(
            $response,
            $response->status(),
            // $response->json()['id']
        );
    }

    public function success(): bool
    {
        return true;
    }

    public function bad(): bool
    {
        return ! $this->success();
    }
}
