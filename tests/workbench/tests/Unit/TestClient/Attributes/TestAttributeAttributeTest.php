<?php

namespace Tests\Unit\TestClient\Attributes;

use App\Http\Clients\TestClient\Attributes\TestAttributeAttribute;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(TestAttributeAttribute::class)]
class TestAttributeAttributeTest extends TestCase
{
    #[Test]
    public function to_array(): void {}
}
