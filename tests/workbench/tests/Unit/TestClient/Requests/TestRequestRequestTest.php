<?php

namespace Tests\Unit\TestClient\Requests;

use App\Http\Clients\TestClient\Attributes\TestRequestAttribute;
use App\Http\Clients\TestClient\Requests\TestRequestRequest;
use App\Http\Clients\TestClient\Responses\BadResponse;
use App\Http\Clients\TestClient\Responses\TestRequestResponse;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[CoversClass(TestRequestRequest::class)]
class TestRequestRequestTest extends TestCase
{
    /** @var TestRequestAttribute */
    public $attributes;

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();

        $this->attributes = new TestRequestAttribute;
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

        $TestRequestRequest = app(TestRequestRequest::class);

        $this->assertInstanceOf(
            TestRequestResponse::class,
            $TestRequestRequest->send('api-key', $this->webhookId, $this->attributes)
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

        $TestRequestRequest = app(TestRequestRequest::class);

        $this->assertInstanceOf(
            BadResponse::class,
            $TestRequestRequest->send('api-key', $this->webhookId, $this->attributes)
        );

        Http::assertSentCount(1);
    }
}
