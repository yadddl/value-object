<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Primitive;

use Yadddl\ValueObject\Contracts\ValidatedValue;
use Yadddl\ValueObject\Error\ErrorsBag;
use Yadddl\ValueObject\Error\Failable;
use Yadddl\ValueObject\Error\FailableSuccess;
use Yadddl\ValueObject\Validators\StringValue;

readonly class Text implements Failable, ValidatedValue, \Stringable
{
    use FailableSuccess;
    use LabeledPrimitive;

    /** @var non-empty-string */
    protected const REGEX = '/(.+)/';

    final private function __construct(public string $value)
    {
    }

    public static function create(mixed $value): static|ErrorsBag
    {
        $context = static::validate($value);

        return $context->isValid()
            ? new static($context->getValue())
            : static::failuresFrom($context);
    }

    public static function validate(mixed $value): StringValue
    {
        /** @var non-empty-string $regex */
        $regex = static::REGEX;
        $label = static::label();

        return $value
            |> StringValue::from(...)
            |> StringValue::matches(
                $regex,
                sprintf("%s is invalid: value does not match '%s'", $label, $regex),
            );
    }

    public function toUpperCase(): static
    {
        return new static(strtoupper($this->value));
    }

    public function toLowerCase(): static
    {
        return new static(strtolower($this->value));
    }

    public function equals(self $other): bool
    {
        return $other instanceof static && $other->value === $this->value;
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->value;
    }
}
