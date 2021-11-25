<?php

require_once (__DIR__ . '/../vendor/autoload.php');

use Yadddl\DDD\Error\ValidationError;
use Yadddl\DDD\Examples\Age;
use Yadddl\DDD\Primitive\Text;
use Yadddl\DDD\Serializer\SerializerBaseFactory;
use function Yadddl\DDD\factory;

// Getting the base serializer (you can customize it, if you want)
$serializer = SerializerBaseFactory::make();

class Person {
    private function __construct(private FullName $fullName, private Age $age )
    {
    }

    public function getAge(): Age
    {
        return $this->age;
    }

    public function getFullName(): FullName
    {
        return $this->fullName;
    }

    public static function create(string $name, string $surname, int $age): static|ValidationError {
        return factory(__CLASS__, FullName::create($name, $surname), Age::create($age));
    }
}

class Person {
    private function __construct(private FullName $fullName, private Age $age )
    {
    }



    public function getAge(): Age
    {
        return $this->age;
    }

    public function getFullName(): FullName
    {
        return $this->fullName;
    }

    public static function create(string $name, string $surname, int $age): static|ValidationError {
        return factory(__CLASS__, FullName::create($name, $surname), Age::create($age));
    }
}

class Name extends Text {
    protected string $regex = "/^[A-Za-z]{2,20}$/";
}

class FullName {
    public function __construct(private Name $firstName, private Name $lastName)
    {
    }
    public function getFirstName(): Name
    {
        return $this->firstName;
    }

    public function getLastName(): Name
    {
        return $this->lastName;
    }

    public static function create(string $name, string $surname): static|ValidationError {
        return factory(
            __CLASS__,
            Name::create($name),
            Name::create($surname)
        );
    }
}


$person = Person::create('Stefano con il nome lungo lungo lungo ', 'Fago con il cognome lungo lungo ', '210');

if ($person instanceof ValidationError) {
    print_r($person->getInvalidFields());
}else {

    print_r($serializer->serialize($person));
}


