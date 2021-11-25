<?php

declare(strict_types=1);

namespace Yadddl\DDD\Examples;

use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;
use Stringable;

#[Immutable]
final class Address implements Stringable
{
    private function __construct(private string $street, private string $postalCode, private string $city, private string $country)
    {
    }

    public function __toString()
    {
        return "{$this->street} {$this->postalCode} {$this->city} {$this->country}";
    }
}
