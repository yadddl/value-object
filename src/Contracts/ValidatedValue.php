<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Contracts;

/**
 * Value objects produced by successful validation expose their primitive via {@see $value}.
 */
interface ValidatedValue
{
    public mixed $value { get; }
}
