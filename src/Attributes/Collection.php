<?php

namespace Yadddl\ValueObject\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
readonly class Collection
{
    public function __construct(public string $className) {}
}