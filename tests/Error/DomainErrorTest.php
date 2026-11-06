<?php

declare(strict_types=1);

use Yadddl\ValueObject\Error\DomainError;
use Yadddl\ValueObject\Error\InvalidEntity;
use Yadddl\ValueObject\Error\InvalidField;
use Yadddl\ValueObject\Error\InvalidValue;

test('InvalidValue is a DomainError under InvalidField', function () {
    $error = new InvalidValue('Something went wrong');

    expect($error)->toBeInstanceOf(DomainError::class)
        ->and($error)->toBeInstanceOf(InvalidField::class)
        ->and($error)->toBeInstanceOf(\RuntimeException::class)
        ->and($error->getMessage())->toBe('Something went wrong');
});

test('InvalidEntity subclasses sit under DomainError', function () {
    $error = new class ('illegal state') extends InvalidEntity {};

    expect($error)->toBeInstanceOf(DomainError::class)
        ->and($error)->toBeInstanceOf(InvalidEntity::class)
        ->and($error->getMessage())->toBe('illegal state');
});
