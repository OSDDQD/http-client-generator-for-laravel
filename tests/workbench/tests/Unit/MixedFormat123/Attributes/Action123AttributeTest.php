<?php

namespace Tests\Unit\MixedFormat123\Attributes;

use App\Http\Clients\MixedFormat123\Attributes\Action123Attribute;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Action123Attribute::class)]
class Action123AttributeTest extends TestCase
{
    #[Test]
    public function to_array(): void {}
}
