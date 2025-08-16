<?php

namespace Tests\Unit\Twitter\Attributes;

use App\Http\Clients\Twitter\Attributes\FetchTweetsAttribute;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(FetchTweetsAttribute::class)]
class FetchTweetsAttributeTest extends TestCase
{
    #[Test]
    public function to_array(): void {}
}
