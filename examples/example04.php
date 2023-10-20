<?php

require_once (__DIR__ . '/../vendor/autoload.php');

use Yadddl\ValueObject\Error\ValidationError;
use Yadddl\ValueObject\Primitive\PositiveInteger;
use Yadddl\ValueObject\Primitive\Text;
use function Yadddl\ValueObject\factory;


readonly class ExampleText extends Text {
    protected const  REGEX = '/^ciao/';
}

$text = ExampleText::create('ciao mondo');

if ($text instanceof \Yadddl\ValueObject\Error\InvalidValueObject) {
    var_dump($text->getMessage()); // LEFT
}
else {
    var_dump((string) $text); // RIGHT
}

$serializer = \Yadddl\ValueObject\Serializer\SerializerBaseFactory::make();

class Pippo {
    public function __construct(private ExampleText $name, private PositiveInteger $age)
    {

    }

    public function getAge(): PositiveInteger
    {
        return $this->age;
    }

    public function getName(): ExampleText
    {
        return $this->name;
    }

    /**
     * @throws ReflectionException
     */
    public static function  create(string $name, int $age): static | ValidationError {
        return factory(
            __CLASS__,
            ExampleText::create($name),
            PositiveInteger::create($age)
        );
    }
}

$pippo = Pippo::create('ciao Pippo', 10);

if ($text instanceof \Yadddl\ValueObject\Error\ValidationError) {
    var_dump($text->getInvalidFields()); // LEFT
}
else {
    var_dump($serializer->serialize($text)); // RIGHT
}

Pippo::create (...$data);
Pippo::create (name: 'pipppo', age: 'fff');

interface DTO {
    public static function create(): static;
}

class Test implements DTO {

    public static function create(string $a): static
    {

    }
}