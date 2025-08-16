<?php

namespace App\Http\Clients\CamelCase\Attributes;

class CamelActionAttribute
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
