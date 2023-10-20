<?php

use Yadddl\ValueObject\Error\ValidationError;
use Yadddl\ValueObject\Primitive\Integer;
use Yadddl\ValueObject\Primitive\NotEmptyText;
use Yadddl\ValueObject\Primitive\PositiveInteger;
use Yadddl\ValueObject\Primitive\Text;
use Yadddl\ValueObject\Unit\Examples\ComplexCompositeObject;
use Yadddl\ValueObject\Unit\Examples\IterableCompositeObject;
use Yadddl\ValueObject\Unit\Examples\SimpleCompositeObject;


function factory(string $type, mixed $data)
{
    $builder = new \Yadddl\ValueObject\Factory\BuilderImpl();

    return $builder->build($type, $data);
}

it('Should build a simple Text object', function () {
    $text = 'Hello, World!';

    $result = factory(Text::class, $text);

    expect($result)->toBeInstanceOf(Text::class);
    expect($result->value)->toBe($text);
});

it('Should build a simple Integer object', function () {
    $int = 42;

    $result = factory(Integer::class, $int);

    expect($result)->toBeInstanceOf(Integer::class);
    expect($result->value)->toBe($int);
});

it('Should give an error in case of negative number', function () {
    $int = -42;

    $result = factory(PositiveInteger::class, $int);

    expect($result)->toBeInstanceOf(ValidationError::class)
        ->and($result->getInvalidFields())->toBe([
            'class' => 'Yadddl\ValueObject\Primitive\PositiveInteger',
            'shortName' => 'PositiveInteger',
            'message' => 'Invalid PositiveInteger',
            'fields' => [
                'value' => 'The value -42 is too small. Minimum 0',
            ]
        ]);
});

it('Should give an error in case of empty text', function () {
    $result = factory(NotEmptyText::class, '');

    expect($result)->toBeInstanceOf(ValidationError::class)
        ->and($result->getInvalidFields())->toBe([
            'class' => 'Yadddl\ValueObject\Primitive\NotEmptyText',
            'shortName' => 'NotEmptyText',
            'message' => 'Invalid NotEmptyText',
            'fields' => [
                'value' => "Invalid string: '' does not match with '/./'",
            ]
        ]);
});

it('Should build a simple composite object', function () {
    $result = factory(SimpleCompositeObject::class, ['name' => 'Pino Insegno', "age" => 42]);

    //  expect($result->getInvalidFields())->toBe('');

    expect($result)->toBeInstanceOf(SimpleCompositeObject::class)
        ->and($result->name->value)->toBe('Pino Insegno')
        ->and($result->age->value)->toBe(42);
});

it('Should give an error in case of missing fields on simple composite object', function () {
    $result = factory(SimpleCompositeObject::class, []);

    expect($result)->toBeInstanceOf(ValidationError::class)
        ->and($result->getInvalidFields())->toBe([
            'class' => 'Yadddl\ValueObject\Unit\Examples\SimpleCompositeObject',
            'shortName' => 'SimpleCompositeObject',
            'message' => 'Invalid SimpleCompositeObject',
            'fields' => [
                'name' => 'Field name is missing',
                'age' => 'Field age is missing',
            ],
        ]);
});

it('Should give an error in case of wrong data on simple composite object', function () {
    /** @var ValidationError|SimpleCompositeObject $result */
    $result = factory(SimpleCompositeObject::class, ['age' => -10, 'name' => '']);

    expect($result)->toBeInstanceOf(ValidationError::class)
        ->and($result->getInvalidFields())->toBe([
            'class' => 'Yadddl\ValueObject\Unit\Examples\SimpleCompositeObject',
            'shortName' => 'SimpleCompositeObject',
            'message' => 'Invalid SimpleCompositeObject',
            'fields' => [
                'name.value' => "Invalid string: '' does not match with '/./'",
                'age.value' => 'The value -10 is too small. Minimum 0',
            ],
        ]);
});

it('Should not broke in case of  an error in case of missing fields on simple composite object', function () {
    /** @var SimpleCompositeObject $result */
    $result = factory(SimpleCompositeObject::class, ["name" => 'Pino Insegno', "age" => 42, "test" => 'eee']);

    expect($result)->toBeInstanceOf(SimpleCompositeObject::class)
        ->and($result->name->value)->toBe('Pino Insegno')
        ->and($result->age->value)->toBe(42);
});

it('Should build a complex composite object', function () {
    /** @var ComplexCompositeObject $result */
    $result = factory(ComplexCompositeObject::class, [
        'name' => 'Pino Insegno',
        "age" => 42,
        'child' => [
            'name' => 'Mario Insegno',
            'age' => 18
        ]
    ]);

    //  expect($result->getInvalidFields())->toBe('');

    expect($result)->toBeInstanceOf(ComplexCompositeObject::class)
        ->and($result->name->value)->toBe('Pino Insegno')
        ->and($result->age->value)->toBe(42)
        ->and($result->child->name->value)->toBe('Mario Insegno')
        ->and($result->child->age->value)->toBe(18);
});

it('Should give an error in case of missing fields on complex object', function () {
    $result = factory(ComplexCompositeObject::class, []);

    expect($result)->toBeInstanceOf(ValidationError::class)
        ->and($result->getInvalidFields())->toBe([
            'class' => 'Yadddl\ValueObject\Unit\Examples\ComplexCompositeObject',
            'shortName' => 'ComplexCompositeObject',
            'message' => 'Invalid ComplexCompositeObject',
            'fields' => [
                'name' => 'Field name is missing',
                'age' => 'Field age is missing',
                'child' => 'Field child is missing',
            ],
        ]);
});

it('Should give an error in case of missing fields on complex object 2', function () {
    $result = factory(ComplexCompositeObject::class, ['child' => []]);

    expect($result)->toBeInstanceOf(ValidationError::class)
        ->and($result->getInvalidFields())->toBe([
            'class' => 'Yadddl\ValueObject\Unit\Examples\ComplexCompositeObject',
            'shortName' => 'ComplexCompositeObject',
            'message' => 'Invalid ComplexCompositeObject',
            'fields' => [
                'name' => 'Field name is missing',
                'age' => 'Field age is missing',
                'child.name' => 'Field name is missing',
                'child.age' => 'Field age is missing',
            ],
        ]);
});

it('Should give an error in case of missing fields on complex object 3', function () {
    $result = factory(ComplexCompositeObject::class, ['age' => 42, 'name' => 'Pippo', 'child' => []]);

    expect($result)->toBeInstanceOf(ValidationError::class)
        ->and($result->getInvalidFields())->toBe([
            'class' => 'Yadddl\ValueObject\Unit\Examples\ComplexCompositeObject',
            'shortName' => 'ComplexCompositeObject',
            'message' => 'Invalid ComplexCompositeObject',
            'fields' => [
                'child.name' => 'Field name is missing',
                'child.age' => 'Field age is missing',
            ],
        ]);
});

it('Should give an error in case of missing fields and errors on complex object', function () {
    $result = factory(ComplexCompositeObject::class, ['age' => -20, 'child' => ['name' => '']]);

    expect($result)->toBeInstanceOf(ValidationError::class)
        ->and($result->getInvalidFields())->toBe([
            'class' => 'Yadddl\ValueObject\Unit\Examples\ComplexCompositeObject',
            'shortName' => 'ComplexCompositeObject',
            'message' => 'Invalid ComplexCompositeObject',
            'fields' => [
                'name' => 'Field name is missing',
                'age.value' => 'The value -20 is too small. Minimum 0',
                'child.name.value' => "Invalid string: '' does not match with '/./'",
                'child.age' => 'Field age is missing',
            ],
        ]);
});

it('Should build an IterableCompositeObject', function () {
    /** @var IterableCompositeObject $result */
    $result = factory(IterableCompositeObject::class, [
        'name' => 'Pino Insegno',
        "age" => 42,
        'children' => [
            ['name' => 'Mario Insegno', 'age' => 18],
            ['name' => 'Gino Insegno', 'age' => 12]
        ]
    ]);

    expect($result)->toBeInstanceOf(IterableCompositeObject::class)
        ->and($result->name->value)->toBe('Pino Insegno')
        ->and($result->age->value)->toBe(42)
        ->and($result->children[0]->name->value)->toBe('Mario Insegno')
        ->and($result->children[0]->age->value)->toBe(18)
        ->and($result->children[1]->name->value)->toBe('Gino Insegno')
        ->and($result->children[1]->age->value)->toBe(12);
});

it('Should give an error in case of missing fields on an IterableCompositeObject', function () {
    $result = factory(IterableCompositeObject::class, ['age' => 42, 'name' => 'Pippo', 'children' => [[]]]);

    expect($result)->toBeInstanceOf(ValidationError::class)
        ->and($result->getInvalidFields())->toBe([
            'class' => 'Yadddl\ValueObject\Unit\Examples\IterableCompositeObject',
            'shortName' => 'IterableCompositeObject',
            'message' => 'Invalid IterableCompositeObject',
            'fields' => [
                'children.[0].name' => 'Field name is missing',
                'children.[0].age' => 'Field age is missing',
            ],
        ]);
});

it('Should give an error in case of wrong data on an IterableCompositeObject', function () {
    $result = factory(IterableCompositeObject::class, ['age' => 42, 'name' => 'Pippo', 'children' => [["age" => -10, 'name' => '']]]);

    expect($result)->toBeInstanceOf(ValidationError::class)
        ->and($result->getInvalidFields())->toBe([
            'class' => 'Yadddl\ValueObject\Unit\Examples\IterableCompositeObject',
            'shortName' => 'IterableCompositeObject',
            'message' => 'Invalid IterableCompositeObject',
            'fields' => [
                'children.[0].name.value' => "Invalid string: '' does not match with '/./'",
                'children.[0].age.value' => 'The value -10 is too small. Minimum 0',
            ],
        ]);
});
