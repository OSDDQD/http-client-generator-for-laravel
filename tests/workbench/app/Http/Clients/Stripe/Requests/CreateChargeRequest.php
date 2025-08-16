<?php

namespace App\Http\Clients\Stripe\Requests;

use App\Http\Clients\Stripe\Attributes\CreateChargeAttribute;
use App\Http\Clients\Stripe\Responses\BadResponse;
use App\Http\Clients\Stripe\Responses\CreateChargeResponse;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Response;

class CreateChargeRequest
{
    public function __construct(public Factory $client) {}

    public function send(CreateChargeAttribute $attribute): BadResponse|CreateChargeResponse
    {
        // $response = $this->client->post('users/create', $attribute->toArray());

        if ($response->status() === Response::HTTP_NO_CONTENT) {
            return CreateChargeResponse::fromResponse($response);
        }

        return BadResponse::fromResponse($response);
    }
}
