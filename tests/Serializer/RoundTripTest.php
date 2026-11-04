<?php

declare(strict_types=1);

use Tests\Serializer\Fixtures\Address;
use Tests\Serializer\Fixtures\Customer;
use Yadddl\Serializer\StructuralConverter;
use Yadddl\Serializer\StructuralSerializer;
use Yadddl\Serializer\FactoryDeserializer;
use Yadddl\ValueObject\Composite\Date;
use Yadddl\ValueObject\Composite\DateTime;
use Yadddl\ValueObject\Composite\Time;
use Yadddl\ValueObject\Examples\Age;
use Yadddl\ValueObject\Examples\Person;
use Yadddl\ValueObject\Primitive\Email;
use Yadddl\ValueObject\Primitive\Integer;
use Yadddl\ValueObject\Primitive\PositiveInteger;
use Yadddl\ValueObject\Primitive\Text;

test('round-trips simple value objects', function (string $type, mixed $input) {
    $converter = new StructuralConverter();
    $original = $type::create($input)->orFail();
    $payload = $converter->toArray($original);
    $restored = $converter->fromArray($payload, $type);

    expect($restored)->toBeInstanceOf($type)
        ->and($restored->value)->toBe($original->value);
})->with([
    'age' => [Age::class, 40],
    'text' => [Text::class, 'hello'],
    'integer' => [Integer::class, 12],
    'positive' => [PositiveInteger::class, 0],
    'email' => [Email::class, 'user@example.com'],
]);

test('round-trips person composite', function () {
    $converter = new StructuralConverter();
    $original = Person::create('Jane Doe', 'jane@example.com', 28)->orFail();
    $payload = $converter->toArray($original);
    $restored = $converter->fromArray($payload, Person::class);

    expect($payload)->toBe([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'age' => 28,
    ])
        ->and($restored)->toBeInstanceOf(Person::class)
        ->and($restored->name)->toBe($original->name)
        ->and($restored->email)->toBe($original->email)
        ->and($restored->age)->toBe($original->age);
});

test('round-trips date time family via property graph', function () {
    $converter = new StructuralConverter(new StructuralSerializer(), new FactoryDeserializer());
    $date = Date::create('2024-05-06')->orFail();
    $time = Time::create('07:08:09')->orFail();
    $dateTime = DateTime::createFromDateAndTime($date, $time);

    expect($converter->fromArray($converter->toArray($date), Date::class)->equals($date))->toBeTrue()
        ->and($converter->fromArray($converter->toArray($time), Time::class)->equals($time))->toBeTrue()
        ->and($converter->fromArray($converter->toArray($dateTime), DateTime::class)->equals($dateTime))->toBeTrue();
});

test('round-trips date time family via string defaults', function () {
    $converter = StructuralConverter::create();
    $date = Date::create('2024-05-06')->orFail();
    $time = Time::create('07:08:09')->orFail();
    $dateTime = DateTime::create('2024-05-06 07:08:09')->orFail();

    expect($converter->toArray($date))->toBe('2024-05-06')
        ->and($converter->fromArray('2024-05-06', Date::class)->equals($date))->toBeTrue()
        ->and($converter->toArray($time))->toBe('07:08:09')
        ->and($converter->fromArray('07:08:09', Time::class)->equals($time))->toBeTrue()
        ->and($converter->toArray($dateTime))->toBe('2024-05-06 07:08:09')
        ->and($converter->fromArray('2024-05-06 07:08:09', DateTime::class)->equals($dateTime))->toBeTrue();
});

test('round-trips nested customer graph', function () {
    $converter = new StructuralConverter();
    $original = Customer::create(
        'Ada',
        'ada@example.com',
        Address::create('London', 'Baker St')->orFail(),
    )->orFail();

    $payload = $converter->toArray($original);
    $restored = $converter->fromArray($payload, Customer::class);

    expect($payload)->toBe([
        'name' => 'Ada',
        'email' => 'ada@example.com',
        'address' => [
            'city' => 'London',
            'street' => 'Baker St',
        ],
    ])
        ->and($restored)->toBeInstanceOf(Customer::class)
        ->and($restored->address->street)->toBe('Baker St');
});
