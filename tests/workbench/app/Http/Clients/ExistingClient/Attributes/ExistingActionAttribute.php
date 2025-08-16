<?php

namespace App\Http\Clients\ExistingClient\Attributes;

class ExistingActionAttribute
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
