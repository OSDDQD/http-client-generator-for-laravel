<?php

namespace Tests\Unit\\Attributes;

use App\Http\Clients\\Attributes\TestActionAttribute;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(TestActionAttribute::class)]
class TestActionAttributeTest extends TestCase
{
    #[Test]
    public function to_array(): void
    {

    }
}
