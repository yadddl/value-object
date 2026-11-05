<?php

declare(strict_types=1);

namespace Tests\Support;

use Yadddl\ValueObject\Contracts\ValidatedValue;
use Yadddl\ValueObject\Error\ErrorsBag;
use Yadddl\ValueObject\Error\Failable;
use Yadddl\ValueObject\Error\FailableSuccess;
use Yadddl\ValueObject\Validators\IntegerValue;

readonly final class SampleAge implements Failable, ValidatedValue
{
    use FailableSuccess;

    private function __construct(public int $value)
    {
    }

    public static function create(mixed $value): self|ErrorsBag
    {
        $context = $value
            |> IntegerValue::from(...)
            |> IntegerValue::min(0, 'Age cannot be negative');

        return $context->isValid()
            ? new self($context->getValue())
            : $context->getErrors();
    }
}
