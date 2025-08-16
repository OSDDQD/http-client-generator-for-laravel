<?php

namespace App\Http\Clients\Special-Client\Attributes;

class Action_With_UnderscoresAttribute
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
