<?php

namespace App\Http\Clients\SyntaxTest\Requests;

use App\Http\Clients\SyntaxTest\Attributes\ValidateCodeAttribute;
use App\Http\Clients\SyntaxTest\Responses\BadResponse;
use App\Http\Clients\SyntaxTest\Responses\ValidateCodeResponse;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Response;

class ValidateCodeRequest
{
    public function __construct(public Factory $client) {}

    public function send(ValidateCodeAttribute $attribute): BadResponse|ValidateCodeResponse
    {
        // $response = $this->client->post('users/create', $attribute->toArray());

        if ($response->status() === Response::HTTP_NO_CONTENT) {
            return ValidateCodeResponse::fromResponse($response);
        }

        return BadResponse::fromResponse($response);
    }
}
