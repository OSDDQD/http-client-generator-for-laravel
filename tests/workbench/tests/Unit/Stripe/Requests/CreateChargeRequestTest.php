<?php

namespace Tests\Unit\Stripe\Requests;

use App\Http\Clients\Stripe\Attributes\CreateChargeAttribute;
use App\Http\Clients\Stripe\Requests\CreateChargeRequest;
use App\Http\Clients\Stripe\Responses\BadResponse;
use App\Http\Clients\Stripe\Responses\CreateChargeResponse;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[CoversClass(CreateChargeRequest::class)]
class CreateChargeRequestTest extends TestCase
{
    /** @var CreateChargeAttribute */
    public $attributes;

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();

        $this->attributes = new CreateChargeAttribute;
    }

    #[Test]
    public function send_successful(): void
    {
        Http::fake([
            '' => Http::response(
                [],
                Response::HTTP_OK
            ),
        ]);

        $CreateChargeRequest = app(CreateChargeRequest::class);

        $this->assertInstanceOf(
            CreateChargeResponse::class,
            $CreateChargeRequest->send('api-key', $this->webhookId, $this->attributes)
        );

        Http::assertSentInOrder([function (Request $request) {
            $this->assertSame(
                '',
                $request->url()
            );

            $this->assertSame('', $request->method());

            $this->assertSame([], $request->data());

            return true;
        },
        ]);
    }

    #[Test]
    public function send_bad(): void
    {
        Http::fake([
            '' => Http::response(
                ['message' => 'invalid', 'code' => Response::HTTP_BAD_REQUEST],
                Response::HTTP_BAD_REQUEST
            ),
        ]);

        $CreateChargeRequest = app(CreateChargeRequest::class);

        $this->assertInstanceOf(
            BadResponse::class,
            $CreateChargeRequest->send('api-key', $this->webhookId, $this->attributes)
        );

        Http::assertSentCount(1);
    }
}
