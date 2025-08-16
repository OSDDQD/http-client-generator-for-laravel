<?php

namespace Tests\Unit\SyntaxTest\Requests;

use App\Http\Clients\SyntaxTest\Attributes\ValidateCodeAttribute;
use App\Http\Clients\SyntaxTest\Requests\ValidateCodeRequest;
use App\Http\Clients\SyntaxTest\Responses\BadResponse;
use App\Http\Clients\SyntaxTest\Responses\ValidateCodeResponse;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[CoversClass(ValidateCodeRequest::class)]
class ValidateCodeRequestTest extends TestCase
{
    /** @var ValidateCodeAttribute */
    public $attributes;

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();

        $this->attributes = new ValidateCodeAttribute;
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

        $ValidateCodeRequest = app(ValidateCodeRequest::class);

        $this->assertInstanceOf(
            ValidateCodeResponse::class,
            $ValidateCodeRequest->send('api-key', $this->webhookId, $this->attributes)
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

        $ValidateCodeRequest = app(ValidateCodeRequest::class);

        $this->assertInstanceOf(
            BadResponse::class,
            $ValidateCodeRequest->send('api-key', $this->webhookId, $this->attributes)
        );

        Http::assertSentCount(1);
    }
}
