<?php

namespace Tests\Unit\CamelCase\Attributes;

use App\Http\Clients\CamelCase\Attributes\CamelActionAttribute;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(CamelActionAttribute::class)]
class CamelActionAttributeTest extends TestCase
{
    #[Test]
    public function to_array(): void {}
}
