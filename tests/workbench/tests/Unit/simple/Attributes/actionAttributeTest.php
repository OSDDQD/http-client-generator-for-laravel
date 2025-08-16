<?php

namespace Tests\Unit\simple\Attributes;

use App\Http\Clients\simple\Attributes\actionAttribute;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(actionAttribute::class)]
class actionAttributeTest extends TestCase
{
    #[Test]
    public function to_array(): void {}
}
