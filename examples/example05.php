<?php


use Yadddl\ValueObject\Primitive\NotEmptyText;
use Yadddl\ValueObject\Primitive\PositiveInteger;

readonly class Person {
    public function __construct(
        public Name $name,
        public PositiveInteger $age
    ) {}
}

readonly class Name {
    public function __construct(
        public NotEmptyText $firstName,
        public NotEmptyText $lastName
    ) {}
}
