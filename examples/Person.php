<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Examples;

use Yadddl\ValueObject\Error\Failable;
use Yadddl\ValueObject\Error\FailableSuccess;
use Yadddl\ValueObject\Error\MultipleFieldErrorsBag;
use Yadddl\ValueObject\Validators\MultipleValidationContext;

final readonly class Person implements Failable
{
    use FailableSuccess;

    private function __construct(
        public string $name,
        public string $email,
        public int $age,
    ) {
    }

    public static function create(
        string $name,
        string $email,
        int $age,
    ): Person|MultipleFieldErrorsBag {
        return MultipleValidationContext::setup(
            name: Name::create($name),
            email: Email::create($email),
            age: Age::create($age),
        )->map(static fn (Name $name, Email $email, Age $age): Person => new Person(
            name: $name->value,
            email: $email->value,
            age: $age->value,
        ));
    }
}
