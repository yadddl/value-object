<?php

declare(strict_types=1);

namespace Tests\Serializer\Fixtures;

use Yadddl\ValueObject\Error\Failable;
use Yadddl\ValueObject\Error\FailableSuccess;
use Yadddl\ValueObject\Error\MultipleFieldErrorsBag;
use Yadddl\ValueObject\Primitive\NotEmptyText;
use Yadddl\ValueObject\Validators\MultipleValidationContext;

readonly final class Address implements Failable
{
    use FailableSuccess;

    private function __construct(
        public string $city,
        public string $street,
    ) {
    }

    public static function create(mixed $city, mixed $street): self|MultipleFieldErrorsBag
    {
        $context = MultipleValidationContext::setup(
            city: NotEmptyText::create($city),
            street: NotEmptyText::create($street),
        );

        return $context->isValid()
            ? new self(...$context->getValues())
            : $context->getErrors();
    }
}
