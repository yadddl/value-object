<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Primitive;

use Yadddl\ValueObject\Error\IntegerTooBig;
use Yadddl\ValueObject\Error\IntegerTooSmall;
use Yadddl\ValueObject\Error\InvalidInteger;

use Yadddl\ValueObject\Error\InvalidValueObject;
use Yadddl\ValueObject\Error\ValueError;
use const PHP_INT_MAX;
use const PHP_INT_MIN;

readonly class Integer
{
    protected const MIN = PHP_INT_MIN;
    protected const MAX = PHP_INT_MAX;

    final public function __construct(public int $value)
    {
        $this->validate($value);
    }

    /**
     * @throws IntegerTooBig
     * @throws IntegerTooSmall
     * @throws InvalidInteger
     */
    protected function validate(int $value): void
    {
        if (!filter_var($value, FILTER_VALIDATE_INT)) {
            throw new InvalidInteger($value, 'value');
        }

        /** @var int $min */
        $min = static::MIN;

        /** @var int $max */
        $max = static::MAX;

        if ($value < $min) {
            throw new IntegerTooSmall($min, $value, 'value');
        }

        if ($value > $max) {
            throw new IntegerTooBig($max, $value, 'value');
        }
    }

    public function __toString(): string
    {
        return (string)$this->value;
    }

    /**
     * @param static $object
     * @return bool
     */
    public function equals(Integer $object): bool
    {
        return $object->value === $this->value;
    }
}
