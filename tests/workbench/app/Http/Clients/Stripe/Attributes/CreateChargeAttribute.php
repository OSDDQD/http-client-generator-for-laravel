<?php

namespace App\Http\Clients\Stripe\Attributes;

class CreateChargeAttribute
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
