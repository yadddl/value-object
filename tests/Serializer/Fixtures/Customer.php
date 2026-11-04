<?php

declare(strict_types=1);

namespace Tests\Serializer\Fixtures;

use Yadddl\ValueObject\Error\ErrorsBag;
use Yadddl\ValueObject\Error\Failable;
use Yadddl\ValueObject\Error\FailableSuccess;
use Yadddl\ValueObject\Error\MultipleFieldErrorsBag;
use Yadddl\ValueObject\Primitive\Email;
use Yadddl\ValueObject\Primitive\NotEmptyText;
use Yadddl\ValueObject\Validators\MultipleValidationContext;

readonly final class Customer implements Failable
{
    use FailableSuccess;

    private function __construct(
        public string $name,
        public string $email,
        public Address $address,
    ) {
    }

    public static function create(mixed $name, mixed $email, Address $address): self|MultipleFieldErrorsBag|ErrorsBag
    {
        $context = MultipleValidationContext::setup(
            name: NotEmptyText::create($name),
            email: Email::create($email),
        );

        if (!$context->isValid()) {
            return $context->getErrors();
        }

        $values = $context->getValues();

        return new self($values['name'], $values['email'], $address);
    }
}
