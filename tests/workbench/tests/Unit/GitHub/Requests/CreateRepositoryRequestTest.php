<?php

namespace Tests\Unit\GitHub\Requests;

use App\Http\Clients\GitHub\Attributes\CreateRepositoryAttribute;
use App\Http\Clients\GitHub\Requests\CreateRepositoryRequest;
use App\Http\Clients\GitHub\Responses\BadResponse;
use App\Http\Clients\GitHub\Responses\CreateRepositoryResponse;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[CoversClass(CreateRepositoryRequest::class)]
class CreateRepositoryRequestTest extends TestCase
{
    /** @var CreateRepositoryAttribute */
    public $attributes;

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();

        $this->attributes = new CreateRepositoryAttribute;
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

        $CreateRepositoryRequest = app(CreateRepositoryRequest::class);

        $this->assertInstanceOf(
            CreateRepositoryResponse::class,
            $CreateRepositoryRequest->send('api-key', $this->webhookId, $this->attributes)
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

        $CreateRepositoryRequest = app(CreateRepositoryRequest::class);

        $this->assertInstanceOf(
            BadResponse::class,
            $CreateRepositoryRequest->send('api-key', $this->webhookId, $this->attributes)
        );

        Http::assertSentCount(1);
    }
}
