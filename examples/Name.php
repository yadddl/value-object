<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Examples;

use Yadddl\ValueObject\Contracts\ValidatedValue;
use Yadddl\ValueObject\Error\ErrorsBag;
use Yadddl\ValueObject\Error\Failable;
use Yadddl\ValueObject\Error\FailableSuccess;
use Yadddl\ValueObject\Validators\StringValue;

final readonly class Name implements Failable, ValidatedValue
{
    use FailableSuccess;

    private function __construct(public string $value)
    {
    }

    public static function create(mixed $value): Name|ErrorsBag
    {
        $context = Name::validate($value);

        return $context->isValid()
            ? new Name($context->getValue())
            : $context->getErrors();
    }

    public static function validate(mixed $value): StringValue
    {
        return $value
                |> StringValue::from(...)
                |> StringValue::minLength(2, "Name cannot be less than 2 characters")
                |> StringValue::maxLength(150, "Name cannot exceed 150 characters");
    }
}
