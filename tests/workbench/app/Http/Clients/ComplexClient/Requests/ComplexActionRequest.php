<?php

namespace App\Http\Clients\ComplexClient\Requests;

use App\Http\Clients\ComplexClient\Attributes\ComplexActionAttribute;
use App\Http\Clients\ComplexClient\Responses\BadResponse;
use App\Http\Clients\ComplexClient\Responses\ComplexActionResponse;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Response;

class ComplexActionRequest
{
    public function __construct(public Factory $client) {}

    public function send(ComplexActionAttribute $attribute): BadResponse|ComplexActionResponse
    {
        // $response = $this->client->post('users/create', $attribute->toArray());

        if ($response->status() === Response::HTTP_NO_CONTENT) {
            return ComplexActionResponse::fromResponse($response);
        }

        return BadResponse::fromResponse($response);
    }
}
