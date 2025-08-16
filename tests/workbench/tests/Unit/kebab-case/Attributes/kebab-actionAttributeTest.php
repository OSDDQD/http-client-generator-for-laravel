<?php

namespace Tests\Unit\kebab-case\Attributes;

use App\Http\Clients\kebab-case\Attributes\kebab-actionAttribute;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(kebab-actionAttribute::class)]
class kebab-actionAttributeTest extends TestCase
{
    #[Test]
    public function to_array(): void
    {

    }
}
