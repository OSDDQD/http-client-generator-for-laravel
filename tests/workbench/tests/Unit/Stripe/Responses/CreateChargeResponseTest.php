<?php

namespace Tests\Unit\Stripe\Responses;

use Illuminate\Http\Response;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use GuzzleHttp\Psr7\Response as Psr7Response;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Http\Clients\Stripe\Responses\CreateChargeResponse;
use Illuminate\Http\Client\Response as ClientResponse;

#[CoversClass(CreateChargeResponse::class)]
class CreateChargeResponseTest extends TestCase
{
    /** @var ClientResponse */
    public $response;

    public function setUp(): void
    {
        parent::setUp();

        $psr7Response = new Psr7Response(
            status: Response::HTTP_CREATED,
            body: json_encode(/* */)
        );

        $this->response = new ClientResponse($psr7Response);
    }

    #[Test]
    public function from_response(): void
    {
        $createResponse = CreateChargeResponse::fromResponse($this->response);

        $this->assertSame(/* Response::HTTP_CREATED */, $createResponse->status);

        $this->assertSame($this->response, $createResponse->original);
    }


}
