<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Primitive;

use Yadddl\ValueObject\Contracts\ValidatedValue;
use Yadddl\ValueObject\Error\ErrorsBag;
use Yadddl\ValueObject\Error\Failable;
use Yadddl\ValueObject\Error\FailableSuccess;
use Yadddl\ValueObject\Validators\IntegerValue;

readonly class Integer implements Failable, ValidatedValue, \Stringable
{
    use FailableSuccess;

    protected const MIN = \PHP_INT_MIN;
    protected const MAX = \PHP_INT_MAX;

    final private function __construct(public int $value)
    {
    }

    public static function create(mixed $value): static|ErrorsBag
    {
        $context = static::validate($value);

        return $context->isValid()
            ? new static($context->getValue())
            : $context->getErrors();
    }

    public static function validate(mixed $value): IntegerValue
    {
        return $value
            |> IntegerValue::from(...)
            |> IntegerValue::min(static::MIN, sprintf('Value must be at least %d', static::MIN))
            |> IntegerValue::max(static::MAX, sprintf('Value must be at most %d', static::MAX));
    }

    public function equals(self $other): bool
    {
        return $other instanceof static && $other->value === $this->value;
    }

    #[\Override]
    public function __toString(): string
    {
        return (string) $this->value;
    }
}
