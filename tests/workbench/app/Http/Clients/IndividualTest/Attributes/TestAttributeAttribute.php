<?php

namespace App\Http\Clients\IndividualTest\Attributes;

class TestAttributeAttribute
{
    public function __construct(
        /* protected string $title, */
    ) {}

    public function toArray(): array
    {
        return [
            'title' => $this->title,
        ];
    }
}
