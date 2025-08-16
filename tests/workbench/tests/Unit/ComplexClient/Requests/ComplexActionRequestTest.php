<?php

namespace Tests\Unit\ComplexClient\Requests;

use App\Http\Clients\ComplexClient\Attributes\ComplexActionAttribute;
use App\Http\Clients\ComplexClient\Requests\ComplexActionRequest;
use App\Http\Clients\ComplexClient\Responses\BadResponse;
use App\Http\Clients\ComplexClient\Responses\ComplexActionResponse;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[CoversClass(ComplexActionRequest::class)]
class ComplexActionRequestTest extends TestCase
{
    /** @var ComplexActionAttribute */
    public $attributes;

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();

        $this->attributes = new ComplexActionAttribute;
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

        $ComplexActionRequest = app(ComplexActionRequest::class);

        $this->assertInstanceOf(
            ComplexActionResponse::class,
            $ComplexActionRequest->send('api-key', $this->webhookId, $this->attributes)
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

        $ComplexActionRequest = app(ComplexActionRequest::class);

        $this->assertInstanceOf(
            BadResponse::class,
            $ComplexActionRequest->send('api-key', $this->webhookId, $this->attributes)
        );

        Http::assertSentCount(1);
    }
}
