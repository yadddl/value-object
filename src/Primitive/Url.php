<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Primitive;

use Yadddl\ValueObject\Contracts\ValidatedValue;
use Yadddl\ValueObject\Error\ErrorsBag;
use Yadddl\ValueObject\Error\Failable;
use Yadddl\ValueObject\Error\FailableSuccess;
use Yadddl\ValueObject\Validators\StringValue;

final readonly class Url implements Failable, ValidatedValue, \Stringable
{
    use FailableSuccess;

    private function __construct(public string $value)
    {
    }

    public static function create(mixed $value): self|ErrorsBag
    {
        $context = self::validate($value);

        return $context->isValid()
            ? new self($context->getValue())
            : $context->getErrors();
    }

    public static function validate(mixed $value): StringValue
    {
        return $value
            |> StringValue::from(...)
            |> StringValue::url('Invalid URL format');
    }

    public function equals(self $other): bool
    {
        return $other->value === $this->value;
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->value;
    }
}
