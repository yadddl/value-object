<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Primitive;

use Yadddl\ValueObject\Error\IntegerTooBig;
use Yadddl\ValueObject\Error\IntegerTooSmall;
use Yadddl\ValueObject\Error\InvalidInteger;
use const PHP_INT_MAX;
use const PHP_INT_MIN;

readonly class Integer extends Primitive
{
    protected const MIN = PHP_INT_MIN;
    protected const MAX = PHP_INT_MAX;

    /**
     * @throws IntegerTooBig
     * @throws IntegerTooSmall
     * @throws InvalidInteger
     */
    protected function validate(int|float|string|bool $value): void
    {
        if (!filter_var($value, FILTER_VALIDATE_INT)) {
            throw new InvalidInteger($value);
        }

        /** @var int $min */
        $min = static::MIN;

        /** @var int $max */
        $max = static::MAX;

        if ($value < $min) {
            throw new IntegerTooSmall($min, $value);
        }

        if ($value > $max) {
            throw new IntegerTooBig($max, $value);
        }
    }

    protected function cast(float|bool|int|string $value): int
    {
        return (int)$value;
    }
}
