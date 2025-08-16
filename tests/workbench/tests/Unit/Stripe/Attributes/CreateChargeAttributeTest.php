<?php

namespace Tests\Unit\Stripe\Attributes;

use App\Http\Clients\Stripe\Attributes\CreateChargeAttribute;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(CreateChargeAttribute::class)]
class CreateChargeAttributeTest extends TestCase
{
    #[Test]
    public function to_array(): void {}
}
