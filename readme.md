# Http Client Generator for Laravel

This package generates classes for Laravel Http client.

#### Installation
```bash
composer require jcergolj/jcergolj/http-client-generator-for-laravel --dev
```

### Example of usage
```php
$attributes = new FetchAttributes('id', 'name');
$request = app(FetchRequest);
$response = $request->send($attributes);

if ($response->bad()) {
    throw new RequestFailed($response->response->body());
}

$userId = $response->id;
```

## Available commands

### Creates Attributes Class
```bash
php artisan http-client-generator:attribute {client?} {name?}
```

Output
```php
<?php

namespace App\Http\Clients\Trello\Attributes;

class FetchAttribute
{
    public function __construct(
        /* protected string $title, */
    ) {}

    public function toArray(): array
    {
        return [
            'title' => $this->title,
        ];
    }
}

// test
<?php

namespace Tests\Unit\Http\Clients\Trello\Attributes;

use App\Http\Clients\Trello\Attributes\FetchAttribute;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(FetchAttribute::class)]
class FetchAttributeTest extends TestCase
{
    #[Test]
    public function to_array(): void
    {

    }
}
```


### Creates Request Class
```bash
php artisan http-client-generator:request {client?} {name?}
```

Output
```php
<?php

namespace App\Http\Clients\Trello\Requests;

use App\Http\Clients\Trello\Attributes\FetchAttribute;
use App\Http\Clients\Trello\Responses\BadResponse;
use App\Http\Clients\Trello\Responses\FetchResponse;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Response;

class FetchRequest
{
    public function __construct(public Factory $client) {}

    public function send(FetchAttribute $attribute): BadResponse|FetchResponse
    {
        // $response = $this->client->post('users/create', $attribute->toArray());

        if ($response->status() === Response::HTTP_NO_CONTENT) {
            return FetchResponse::fromResponse($response);
        }

        return BadResponse::fromResponse($response);
    }
}

// test
<?php

namespace Tests\Unit\Http\Clients\Trello\Requests;

use Tests\TestCase;
use Illuminate\Http\Response;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Http\Clients\Trello\Responses\BadResponse;
use App\Http\Clients\Trello\Requests\FetchRequest;
use App\Http\Clients\Trello\Responses\FetchResponse;
use App\Http\Clients\Trello\Attributes\SaveInboundWebhookAttribute;

#[CoversClass(FetchRequest::class)]
class FetchRequestTest extends TestCase
{
    /** @var FetchAttribute */
    public $attributes;

    public function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();

        $this->attributes = new FetchAttribute(

        );
    }

    #[Test]
    public function send_successful(): void
    {
        Http::fake([
            "" => Http::response(
                [],
                Response::
            ),
        ]);

        $FetchRequest = app(FetchRequest::class);

        $this->assertInstanceOf(
            FetchResponse::class,
            $FetchRequest->send('api-key', $this->webhookId, $this->attributes)
        );

        Http::assertSentInOrder([function (Request $request) {
            $this->assertSame(
                "",
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
            "" => Http::response(
                ['message' => 'invalid', 'code' => Response::HTTP_BAD_REQUEST],
                Response::HTTP_BAD_REQUEST
            ),
        ]);

        $FetchRequest = app(FetchRequest::class);

        $this->assertInstanceOf(
            BadResponse::class,
            $FetchRequest->send('api-key', $this->webhookId, $this->attributes)
        );

        Http::assertSentCount(1);
    }
}

```

### Creates Response Class
```bash
php artisan http-client-generator:response {client?} {name?}
```

Output
```php
<?php

namespace App\Http\Clients\Trello\Responses;

use App\Http\Clients\HasStatus;
use Illuminate\Http\Client\Response;

class FetchResponse
{
    use HasStatus;

    private function __construct(public Response $original, public int $status, /* public int $id */) {}

    public static function fromResponse(Response $response): self
    {
        return new self(
            $response,
            $response->status(),
            // $response->json()['id']
        );
    }
}


// test
<?php

namespace Tests\Unit\Http\Clients\Trello\Responses;

use Illuminate\Http\Response;
use App\Http\Clients\HasStatus;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use GuzzleHttp\Psr7\Response as Psr7Response;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Http\Clients\Trello\Responses\FetchResponse;
use Illuminate\Http\Client\Response as ClientResponse;

#[CoversClass(FetchResponse::class)]
class FetchResponseTest extends TestCase
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
        $createResponse = FetchResponse::fromResponse($this->response);

        $this->assertSame(/* Response::HTTP_CREATED */, $createResponse->status);

        $this->assertSame($this->response, $createResponse->original);
    }

    #[Test]
    public function asset_class_has_has_status_trait(): void
    {
        $this->assertContains(HasStatus::class, class_uses(FetchResponse::class));
    }
}
```

### Creates Client Macro Class
```bash
php artisan http-client-generator:request {client?}
```

Output
```php
<?php

namespace App\Http\Clients\Twitter;

use Illuminate\Support\Facades\Http;

class TwitterMacro
{
    public function twitter(): callable
    {
        return function () {
            return Http::withHeaders(
                [
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                ]
            )->withUserAgent(/* */)
                ->baseUrl(/* */);
        };
    }
}

// test
<?php

namespace Tests\Unit\Http\Clients\Twitter;

use Tests\TestCase;
use ReflectionClass;
use Illuminate\Support\Facades\Http;
use App\Http\Clients\Twitter\TwitterMacro;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TwitterMacro::class)]
class TwitterMacro extends TestCase
{
    /** @var PendingRequest */
    public $pendingRequest;

    public function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();

        $this->pendingRequest = Http::twitter()->dump();
    }

    #[Test]
    public function assert_http_client_has_twitter_method(): void
    {
        $this->assertTrue(Http::hasMacro('twitter'));
    }

    #[Test]
    public function assert_accept_header_is_set(): void
    {
        $this->assertSame('application/json', $this->pendingRequest->getOptions()['headers']['accept']);
    }

    #[Test]
    public function assert_content_type_header_is_set(): void
    {
        $this->assertSame('application/json', $this->pendingRequest->getOptions()['headers']['content-type']);
    }

    #[Test]
    public function assert_user_agent_header_is_set(): void
    {
        $this->assertSame(/* */, $this->pendingRequest->getOptions()['headers']['User-Agent']);
    }

    #[Test]
    public function assert_base_url_is_set(): void
    {
        $pendingRequest = Http::twitter()->dump();

        $reflectionClass = new ReflectionClass($pendingRequest);
        $baseUrl = $reflectionClass->getProperty('baseUrl');

        $baseUrl->setAccessible(true);

        $this->assertSame(/* */, $baseUrl->getValue($pendingRequest));
    }
}

```

### Creates HasStatus trait
```bash
php artisan http-client-generator:has-status-trait
```

Output
```php
<?php

namespace App\Http\Clients;

trait HasStatus
{
    public function success(): bool
    {
        return true;
    }

    public function bad(): bool
    {
        return ! $this->success();
    }
}

// test
<?php

namespace Tests\Unit\Http\Clients;

use App\Http\Clients\HasStatus;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(HasStatus::class)]
class HasStatusTest extends TestCase
{
    #[Test]
    public function success(): void
    {
        $response = new CustomResponse;
        $this->assertTrue($response->success());
    }

    #[Test]
    public function bad(): void
    {
        $response = new CustomResponse;
        $this->assertFalse($response->bad());
    }
}

class CustomResponse
{
    use HasStatus;
}

```

### Creates BadResponse class
```bash
php artisan http-client-generator:bad-response {client?}
```

Output
```php
<?php

namespace App\Http\Clients\Twitter\Responses;

use Illuminate\Http\Client\Response;

class BadResponse
{
    private function __construct(public Response $response, public int $status, public string $error, public string $code) {}

    public static function fromResponse(Response $response): BadResponse
    {
        return new self(
            $response,
            $response->status(),
            $response->json()['message'] ?? 'no error message provided',
            $response->json()['code'] ?? ''
        );
    }

    public function success(): bool
    {
        return false;
    }

    public function bad(): bool
    {
        return ! $this->success();
    }
}

// test
<?php

namespace Tests\Unit\Http\Clients\Twitter\Responses;

use Illuminate\Http\Response;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use GuzzleHttp\Psr7\Response as Psr7Response;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Http\Clients\Brevo\Responses\BadResponse;
use Illuminate\Http\Client\Response as ClientResponse;

#[CoversClass(BadResponse::class)]
class BadResponseTest extends TestCase
{
    /** @var ClientResponse */
    public $response;

    public function setUp(): void
    {
        parent::setUp();

        $psr7Response = new Psr7Response(
            status: Response::HTTP_BAD_REQUEST,
            body: json_encode(['message' => 'invalid', 'code' => 'bad request'])
        );

        $this->response = new ClientResponse($psr7Response);
    }

    #[Test]
    public function from_response(): void
    {
        $badResponse = BadResponse::fromResponse($this->response);

        $this->assertSame('invalid', $badResponse->error);

        $this->assertSame('bad request', $badResponse->code);

        $this->assertSame(Response::HTTP_BAD_REQUEST, $badResponse->status);
    }

    #[Test]
    public function success(): void
    {
        $badResponse = BadResponse::fromResponse($this->response);

        $this->assertFalse($badResponse->success());
    }

    #[Test]
    public function bad(): void
    {
        $badResponse = BadResponse::fromResponse($this->response);

        $this->assertTrue($badResponse->bad());
    }
}

```

### Creates All
```bash
php artisan http-client-generator:all
```

Generates attribute class and test; request class and test; response class and test; bad response class and test.
