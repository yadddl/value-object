<?php

declare(strict_types=1);

use Tests\Serializer\Fixtures\Address;
use Tests\Serializer\Fixtures\Color;
use Tests\Serializer\Fixtures\Customer;
use Tests\Serializer\Fixtures\NoFactory;
use Tests\Serializer\Fixtures\Priority;
use Yadddl\Serializer\DeserializationError;
use Yadddl\Serializer\FactoryDeserializer;
use Yadddl\ValueObject\Composite\Date;
use Yadddl\ValueObject\Composite\DateTime;
use Yadddl\ValueObject\Composite\DateTimeWithTimeZone;
use Yadddl\ValueObject\Composite\Time;
use Yadddl\ValueObject\Error\ErrorsBag;
use Yadddl\ValueObject\Error\MultipleFieldErrorsBag;
use Yadddl\ValueObject\Examples\Age;
use Yadddl\ValueObject\Examples\Password;
use Yadddl\ValueObject\Examples\Person;
use Yadddl\ValueObject\Primitive\Email;
use Yadddl\ValueObject\Primitive\Integer;
use Yadddl\ValueObject\Primitive\Text;

test('deserializes validated value from scalar via create', function () {
    $deserializer = new FactoryDeserializer();

    expect($deserializer->deserialize(25, Age::class))->toBeInstanceOf(Age::class)
        ->and($deserializer->deserialize(25, Age::class)->value)->toBe(25)
        ->and($deserializer->deserialize('hello', Text::class))->toBeInstanceOf(Text::class)
        ->and($deserializer->deserialize(3, Integer::class))->toBeInstanceOf(Integer::class)
        ->and($deserializer->deserialize('a@b.com', Email::class))->toBeInstanceOf(Email::class);
});

test('propagates errors bag for invalid scalar payload', function () {
    $deserializer = new FactoryDeserializer();

    expect($deserializer->deserialize(-5, Age::class))->toBeInstanceOf(ErrorsBag::class)
        ->and($deserializer->deserialize('weak', Password::class))->toBeInstanceOf(ErrorsBag::class);
});

test('deserializes person from associative array', function () {
    $deserializer = new FactoryDeserializer();
    $person = $deserializer->deserialize([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'age' => 30,
    ], Person::class);

    expect($person)->toBeInstanceOf(Person::class)
        ->and($person->name)->toBe('John Doe')
        ->and($person->email)->toBe('john@example.com')
        ->and($person->age)->toBe(30);
});

test('propagates multiple field errors for invalid person payload', function () {
    $deserializer = new FactoryDeserializer();
    $result = $deserializer->deserialize([
        'name' => '',
        'email' => 'bad',
        'age' => -1,
    ], Person::class);

    expect($result)->toBeInstanceOf(MultipleFieldErrorsBag::class)
        ->and($result->getFieldCount())->toBe(3);
});

test('deserializes date from public parts via createFrom', function () {
    $deserializer = new FactoryDeserializer();
    $date = $deserializer->deserialize([
        'days' => 15,
        'months' => 1,
        'years' => 2024,
    ], Date::class);

    expect($date)->toBeInstanceOf(Date::class)
        ->and((string) $date)->toBe('2024-01-15');
});

test('deserializes time from public parts via createFrom', function () {
    $deserializer = new FactoryDeserializer();
    $time = $deserializer->deserialize([
        'hours' => 8,
        'minutes' => 5,
        'seconds' => 9,
    ], Time::class);

    expect($time)->toBeInstanceOf(Time::class)
        ->and((string) $time)->toBe('08:05:09');
});

test('deserializes nested customer graph through factories', function () {
    $deserializer = new FactoryDeserializer();
    $customer = $deserializer->deserialize([
        'name' => 'Ada',
        'email' => 'ada@example.com',
        'address' => [
            'city' => 'London',
            'street' => 'Baker St',
        ],
    ], Customer::class);

    expect($customer)->toBeInstanceOf(Customer::class)
        ->and($customer->address)->toBeInstanceOf(Address::class)
        ->and($customer->address->city)->toBe('London');
});

test('propagates nested address validation errors', function () {
    $deserializer = new FactoryDeserializer();
    $result = $deserializer->deserialize([
        'name' => 'Ada',
        'email' => 'ada@example.com',
        'address' => [
            'city' => '',
            'street' => '',
        ],
    ], Customer::class);

    expect($result)->toBeInstanceOf(MultipleFieldErrorsBag::class);
});

test('deserializes enums', function () {
    $deserializer = new FactoryDeserializer();

    expect($deserializer->deserialize('red', Color::class))->toBe(Color::Red)
        ->and($deserializer->deserialize('High', Priority::class))->toBe(Priority::High);
});

test('throws deserialization error for unknown types and missing factories', function () {
    $deserializer = new FactoryDeserializer();

    expect(fn () => $deserializer->deserialize([], 'Unknown\\Type'))
        ->toThrow(DeserializationError::class)
        ->and(fn () => $deserializer->deserialize(['value' => 'x'], NoFactory::class))
        ->toThrow(DeserializationError::class);
});

test('throws deserialization error for invalid enum payloads', function () {
    $deserializer = new FactoryDeserializer();

    expect(fn () => $deserializer->deserialize('green', Color::class))
        ->toThrow(DeserializationError::class)
        ->and(fn () => $deserializer->deserialize(1, Priority::class))
        ->toThrow(DeserializationError::class)
        ->and(fn () => $deserializer->deserialize('Nope', Priority::class))
        ->toThrow(DeserializationError::class);
});

test('deserializes datetime from nested date and time parts', function () {
    $deserializer = new FactoryDeserializer();
    $dateTime = $deserializer->deserialize([
        'date' => ['days' => 15, 'months' => 1, 'years' => 2024],
        'time' => ['hours' => 8, 'minutes' => 5, 'seconds' => 9],
    ], DateTime::class);

    expect($dateTime)->toBeInstanceOf(DateTime::class)
        ->and((string) $dateTime)->toBe('2024-01-15 08:05:09');
});

test('deserializes zoned datetime from nested parts', function () {
    $deserializer = new FactoryDeserializer();
    $dateTime = $deserializer->deserialize([
        'date' => ['days' => 15, 'months' => 1, 'years' => 2024],
        'time' => ['hours' => 8, 'minutes' => 5, 'seconds' => 9],
        'timeZone' => 'Europe/Rome',
    ], DateTimeWithTimeZone::class);

    expect($dateTime)->toBeInstanceOf(DateTimeWithTimeZone::class)
        ->and((string) $dateTime)->toBe('2024-01-15 08:05:09 Europe/Rome');
});
