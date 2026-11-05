<?php

declare(strict_types=1);

namespace Tests\Support;

use Yadddl\ValueObject\Contracts\ValidatedValue;
use Yadddl\ValueObject\Error\ErrorsBag;
use Yadddl\ValueObject\Error\Failable;
use Yadddl\ValueObject\Error\FailableSuccess;
use Yadddl\ValueObject\Validators\StringValue;

readonly final class SampleName implements Failable, ValidatedValue
{
    use FailableSuccess;

    private function __construct(public string $value)
    {
    }

    public static function create(mixed $value): self|ErrorsBag
    {
        $context = $value
            |> StringValue::from(...)
            |> StringValue::minLength(2, 'Name too short');

        return $context->isValid()
            ? new self($context->getValue())
            : $context->getErrors();
    }
}
