<?php

it(/**
 * @phpstan-param class-string $className
 * @return void
 * @throws ReflectionException
 */ 'should be a valid Value Object', function (string $className) {
//    $class = new ReflectionClass($className);
//    $createMethod = new ReflectionMethod($className, 'create');

    expect($className)->toHaveMethod('__toString')
        ->and($className)->toHaveMethod('create');

//        ->toHaveMethod('create')
//        ->toBeTrue();
})->with([
    \Yadddl\ValueObject\Primitive\Date::class,
    \Yadddl\ValueObject\Primitive\DateTime::class,
    \Yadddl\ValueObject\Primitive\Integer::class,
    \Yadddl\ValueObject\Primitive\NotEmptyText::class,
    \Yadddl\ValueObject\Primitive\PositiveInteger::class,
    \Yadddl\ValueObject\Primitive\Text::class,
    \Yadddl\ValueObject\Primitive\Time::class,
]);