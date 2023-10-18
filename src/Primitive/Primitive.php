<?php

namespace Yadddl\ValueObject\Primitive;

use Yadddl\ValueObject\Error\InvalidValueObject;
use Yadddl\ValueObject\Error\ValueError;

abstract readonly class Primitive implements \Stringable
{
    public int|float|string|bool $value;

    final private function __construct(int|float|string|bool $value)
    {
        $this->validate($value);

        $this->value = $this->cast($value);
    }

    protected abstract function validate(int|float|string|bool $value): void;

    protected abstract function cast(int|float|string|bool $value): int|float|string|bool;

    public final static function create(self|int|float|string|bool $value): static|InvalidValueObject
    {
        try {

            return is_object($value)
                ? new static ($value->value)
                : new static ($value);

        } catch (ValueError $exception) {
            return new InvalidValueObject($exception->getMessage(), $exception);
        }
    }

    public function __toString(): string
    {
        return (string)$this->value;
    }

    public function equals(Primitive $object): bool {
        return $object instanceof static
            && $object->value === $this->value;
    }
}