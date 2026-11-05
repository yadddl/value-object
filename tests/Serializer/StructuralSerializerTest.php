<?php

declare(strict_types=1);

use Tests\Serializer\Fixtures\Color;
use Tests\Serializer\Fixtures\Priority;
use Yadddl\Serializer\StructuralSerializer;
use Yadddl\ValueObject\Composite\Date;
use Yadddl\ValueObject\Examples\Age;
use Yadddl\ValueObject\Examples\Password;
use Yadddl\ValueObject\Examples\Person;
use Yadddl\ValueObject\Primitive\Integer;
use Yadddl\ValueObject\Primitive\Text;

test('scalars and null pass through', function () {
    $serializer = new StructuralSerializer();

    expect($serializer->serialize(null))->toBeNull()
        ->and($serializer->serialize(1))->toBe(1)
        ->and($serializer->serialize(1.5))->toBe(1.5)
        ->and($serializer->serialize(true))->toBeTrue()
        ->and($serializer->serialize('x'))->toBe('x');
});

test('validated value objects serialize to bare scalars', function () {
    $serializer = new StructuralSerializer();

    expect($serializer->serialize(Age::create(25)->orFail()))->toBe(25)
        ->and($serializer->serialize(Text::create('hi')->orFail()))->toBe('hi')
        ->and($serializer->serialize(Integer::create(7)->orFail()))->toBe(7);
});

test('composites serialize public properties recursively', function () {
    $serializer = new StructuralSerializer();
    $person = Person::create('John Doe', 'john@example.com', 30)->orFail();

    expect($serializer->serialize($person))->toBe([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'age' => 30,
    ]);
});

test('date serializes to public parts by default', function () {
    $serializer = new StructuralSerializer();
    $date = Date::create('2024-01-15')->orFail();

    expect($serializer->serialize($date))->toBe([
        'days' => 15,
        'months' => 1,
        'years' => 2024,
    ]);
});

test('enums serialize to backing value or case name', function () {
    $serializer = new StructuralSerializer();

    expect($serializer->serialize(Color::Red))->toBe('red')
        ->and($serializer->serialize(Priority::High))->toBe('High');
});

test('arrays preserve keys and recurse', function () {
    $serializer = new StructuralSerializer();

    expect($serializer->serialize([
        'age' => Age::create(10)->orFail(),
        2 => Text::create('x')->orFail(),
    ]))->toBe([
        'age' => 10,
        2 => 'x',
    ]);
});

test('traversables become arrays', function () {
    $serializer = new StructuralSerializer();
    $iterator = new ArrayIterator(['a' => Age::create(1)->orFail()]);

    expect($serializer->serialize($iterator))->toBe(['a' => 1]);
});

test('custom type serializers win', function () {
    $serializer = new StructuralSerializer([
        Age::class => static fn (Age $age): string => 'age:'.$age->value,
    ]);

    expect($serializer->serialize(Age::create(9)->orFail()))->toBe('age:9');
});

test('password serializes to hashed string value', function () {
    $serializer = new StructuralSerializer();
    $password = Password::create('MyP@ssw0rd')->orFail();

    expect($serializer->serialize($password))->toBe($password->value)
        ->and(is_string($serializer->serialize($password)))->toBeTrue();
});
