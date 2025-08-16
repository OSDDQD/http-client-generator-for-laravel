<?php

namespace App\Http\Clients\IndividualTest\Requests;

use App\Http\Clients\IndividualTest\Attributes\TestRequestAttribute;
use App\Http\Clients\IndividualTest\Responses\BadResponse;
use App\Http\Clients\IndividualTest\Responses\TestRequestResponse;
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
