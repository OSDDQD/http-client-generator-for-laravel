<?php

namespace App\Http\Clients\kebab-case\Attributes;

class kebab-actionAttribute
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
