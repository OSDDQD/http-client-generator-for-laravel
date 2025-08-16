<?php

namespace Tests\Unit\CustomAPI\Attributes;

use App\External\Clients\CustomAPI\Attributes\TestActionAttribute;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(TestActionAttribute::class)]
class TestActionAttributeTest extends TestCase
{
    #[Test]
    public function to_array(): void {}
}
