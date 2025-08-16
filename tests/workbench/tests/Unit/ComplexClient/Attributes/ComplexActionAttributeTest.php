<?php

namespace Tests\Unit\ComplexClient\Attributes;

use App\Http\Clients\ComplexClient\Attributes\ComplexActionAttribute;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ComplexActionAttribute::class)]
class ComplexActionAttributeTest extends TestCase
{
    #[Test]
    public function to_array(): void {}
}
