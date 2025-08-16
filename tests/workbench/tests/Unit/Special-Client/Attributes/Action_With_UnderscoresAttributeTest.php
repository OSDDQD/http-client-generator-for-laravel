<?php

namespace Tests\Unit\Special-Client\Attributes;

use App\Http\Clients\Special-Client\Attributes\Action_With_UnderscoresAttribute;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Action_With_UnderscoresAttribute::class)]
class Action_With_UnderscoresAttributeTest extends TestCase
{
    #[Test]
    public function to_array(): void
    {

    }
}
