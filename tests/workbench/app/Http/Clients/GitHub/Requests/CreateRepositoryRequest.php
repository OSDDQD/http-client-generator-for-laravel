<?php

namespace App\Http\Clients\GitHub\Requests;

use App\Http\Clients\GitHub\Attributes\CreateRepositoryAttribute;
use App\Http\Clients\GitHub\Responses\BadResponse;
use App\Http\Clients\GitHub\Responses\CreateRepositoryResponse;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Response;

class CreateRepositoryRequest
{
    public function __construct(public Factory $client) {}

    public function send(CreateRepositoryAttribute $attribute): BadResponse|CreateRepositoryResponse
    {
        // $response = $this->client->post('users/create', $attribute->toArray());

        if ($response->status() === Response::HTTP_NO_CONTENT) {
            return CreateRepositoryResponse::fromResponse($response);
        }

        return BadResponse::fromResponse($response);
    }
}
