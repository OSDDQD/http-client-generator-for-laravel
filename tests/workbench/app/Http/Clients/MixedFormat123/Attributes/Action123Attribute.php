<?php

namespace App\Http\Clients\MixedFormat123\Attributes;

class Action123Attribute
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
