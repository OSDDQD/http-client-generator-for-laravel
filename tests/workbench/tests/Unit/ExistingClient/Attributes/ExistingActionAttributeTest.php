<?php

namespace Tests\Unit\ExistingClient\Attributes;

use App\Http\Clients\ExistingClient\Attributes\ExistingActionAttribute;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ExistingActionAttribute::class)]
class ExistingActionAttributeTest extends TestCase
{
    #[Test]
    public function to_array(): void {}
}
