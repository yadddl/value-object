<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Examples;

use Yadddl\ValueObject\Contracts\ValidatedValue;
use Yadddl\ValueObject\Error\ErrorsBag;
use Yadddl\ValueObject\Error\Failable;
use Yadddl\ValueObject\Error\FailableSuccess;
use Yadddl\ValueObject\Validators\IntegerValue;

final readonly class Age implements Failable, ValidatedValue
{
    use FailableSuccess;
    private function __construct(public int $value)
    {
    }

    public static function create(mixed $value): Age|ErrorsBag
    {
        $context = self::validate($value);

        return $context->isValid()
            ? new self($context->getValue())
            : $context->getErrors();
    }

    public static function validate(int $value): IntegerValue
    {
        return $value
            |> IntegerValue::from(...)
            |> IntegerValue::min(0, "Age cannot be negative")
            |> IntegerValue::max(150, "Age cannot exceed 150");
    }
}
