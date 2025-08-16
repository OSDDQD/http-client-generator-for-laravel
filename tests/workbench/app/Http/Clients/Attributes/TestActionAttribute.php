<?php

namespace App\Http\Clients\\Attributes;

class TestActionAttribute
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
