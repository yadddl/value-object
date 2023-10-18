<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Primitive;

use Stringable;
use Yadddl\ValueObject\Error\IntegerTooBig;
use Yadddl\ValueObject\Error\IntegerTooSmall;
use Yadddl\ValueObject\Error\InvalidValueObject;

use const PHP_INT_MAX;
use const PHP_INT_MIN;

class Integer implements Stringable
{
    protected int $min = PHP_INT_MIN;

    protected int $max = PHP_INT_MAX;

    /**
     * @throws IntegerTooBig
     * @throws IntegerTooSmall
     */
    final private function __construct(public int $value)
    {
        if ($this->value < $this->min) {
            throw new IntegerTooSmall($this->min, $value);
        }

        if ($this->value > $this->max) {
            throw new IntegerTooBig($this->max, $value);
        }
    }

    final public static function create(int|string $value): InvalidValueObject|static
    {
        if (!filter_var($value, FILTER_VALIDATE_INT)) {
            return new InvalidValueObject('invalid integer', "Invalid Integer provided: {$value}");
        }

        try {
            return new static((int)$value);
        } catch (IntegerTooSmall $exception) {
            return new InvalidValueObject('integer too small', $exception->getMessage(), $exception);
        } catch (IntegerTooBig $exception) {
            return new InvalidValueObject('integer too big', $exception->getMessage(), $exception);
        }
    }

    public function equalsTo(mixed $integer): bool
    {
        return $integer instanceof static
            && $integer->value === $this->value;
    }

    public function __toString(): string
    {
        return (string)$this->value;
    }
}
