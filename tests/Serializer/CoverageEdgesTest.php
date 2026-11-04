<?php

declare(strict_types=1);

use Tests\Serializer\Fixtures\Address;
use Tests\Serializer\Fixtures\Customer;
use Tests\Serializer\Fixtures\NeedsTraversable;
use Tests\Serializer\Fixtures\OnlyCreateFrom;
use Tests\Serializer\Fixtures\WithOptionalArgs;
use Tests\Serializer\Fixtures\WithStaticProperty;
use Yadddl\Serializer\DeserializationError;
use Yadddl\Serializer\FactoryDeserializer;
use Yadddl\Serializer\StructuralConverter;
use Yadddl\Serializer\StructuralSerializer;
use Yadddl\ValueObject\Examples\Age;

test('converter exposes serializer and deserializer', function () {
    $converter = StructuralConverter::create();

    expect($converter->serializer())->toBeInstanceOf(StructuralSerializer::class)
        ->and($converter->deserializer())->toBeInstanceOf(FactoryDeserializer::class);
});

test('deserializes createFrom-only types from arrays and rejects scalars', function () {
    $deserializer = new FactoryDeserializer();

    expect($deserializer->deserialize(['value' => 5], OnlyCreateFrom::class))->toBeInstanceOf(OnlyCreateFrom::class)
        ->and(fn () => $deserializer->deserialize(5, OnlyCreateFrom::class))
        ->toThrow(DeserializationError::class);
});

test('deserializes single-element list payloads via create', function () {
    $deserializer = new FactoryDeserializer();
    $age = $deserializer->deserialize([40], Age::class);

    expect($age)->toBeInstanceOf(Age::class)
        ->and($age->value)->toBe(40);
});

test('throws when payload keys do not match any factory', function () {
    $deserializer = new FactoryDeserializer();

    expect(fn () => $deserializer->deserialize(['nope' => 1], Age::class))
        ->toThrow(DeserializationError::class);
});

test('fills optional create parameters from defaults', function () {
    $deserializer = new FactoryDeserializer();
    $result = $deserializer->deserialize(['name' => 'Ada'], WithOptionalArgs::class);

    expect($result)->toBeInstanceOf(WithOptionalArgs::class)
        ->and($result->name)->toBe('Ada')
        ->and($result->note)->toBeNull()
        ->and($result->flag)->toBe('default');
});

test('accepts already-built nested objects in payload', function () {
    $deserializer = new FactoryDeserializer();
    $address = Address::create('Rome', 'Via Appia')->orFail();
    $customer = $deserializer->deserialize([
        'name' => 'Ada',
        'email' => 'ada@example.com',
        'address' => $address,
    ], Customer::class);

    expect($customer)->toBeInstanceOf(Customer::class)
        ->and($customer->address)->toBe($address);
});

test('wraps type errors from incompatible factory arguments', function () {
    $deserializer = new FactoryDeserializer();

    expect(fn () => $deserializer->deserialize(['items' => [1, 2]], NeedsTraversable::class))
        ->toThrow(DeserializationError::class);
});

test('skips static public properties during serialize', function () {
    $serializer = new StructuralSerializer();
    $object = new WithStaticProperty('neo');

    expect($serializer->serialize($object))->toBe(['name' => 'neo']);
});

test('passes through resources unchanged', function () {
    $serializer = new StructuralSerializer();
    $resource = fopen('php://memory', 'r');

    expect($serializer->serialize($resource))->toBe($resource);

    fclose($resource);
});
