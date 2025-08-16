<?php

namespace Tests\Unit\snake_case\Attributes;

use App\Http\Clients\snake_case\Attributes\snake_actionAttribute;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(snake_actionAttribute::class)]
class snake_actionAttributeTest extends TestCase
{
    #[Test]
    public function to_array(): void {}
}
