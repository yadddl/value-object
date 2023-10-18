<?php

it('should be a valid Value Object', function (string $className) {
    $class = new ReflectionClass($className);
    $createMethod = new ReflectionMethod($className, 'create');

    expect($class)
        ->toHaveMethod('__toString')
        ->and($createMethod->isStatic())
        ->toBeTrue();
})->with([
    \Yadddl\ValueObject\Primitive\Date::class,
    \Yadddl\ValueObject\Primitive\DateTime::class,
    \Yadddl\ValueObject\Primitive\Integer::class,
    \Yadddl\ValueObject\Primitive\NotEmptyText::class,
    \Yadddl\ValueObject\Primitive\PositiveInteger::class,
    \Yadddl\ValueObject\Primitive\Text::class,
    \Yadddl\ValueObject\Primitive\Time::class,
]);