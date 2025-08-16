<?php

namespace App\Http\Clients\TestClient\Requests;

use App\Http\Clients\TestClient\Attributes\TestRequestAttribute;
use App\Http\Clients\TestClient\Responses\BadResponse;
use App\Http\Clients\TestClient\Responses\TestRequestResponse;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Response;

class TestRequestRequest
{
    public function __construct(public Factory $client) {}

    public function send(TestRequestAttribute $attribute): BadResponse|TestRequestResponse
    {
        // $response = $this->client->post('users/create', $attribute->toArray());

        if ($response->status() === Response::HTTP_NO_CONTENT) {
            return TestRequestResponse::fromResponse($response);
        }

        return BadResponse::fromResponse($response);
    }
}
