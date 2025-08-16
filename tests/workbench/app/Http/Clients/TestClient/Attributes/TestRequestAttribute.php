<?php

namespace App\Http\Clients\TestClient\Attributes;

class TestRequestAttribute
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
