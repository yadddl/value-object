<?php declare(strict_types=1);

require_once(__DIR__ . '/../../vendor/autoload.php');

use Yadddl\ValueObject\Primitive\Text;

// Value Object Definition
class Name extends Text
{
    protected string $regex = "/^[A-Za-z]{2,20}$/"; // Length min:2, max:20. No spaces
}

// Happy Path
$name = Name::create('John'); // it returns a Name

var_dump((string)$name);

echo "Hello {$name}, welcome!\n";

// Wrong Path
$wrongName = Name::create('Wrong name with spaces'); // it returns an InvalidValueObject

var_dump(
    $wrongName->getMessage(),
    $wrongName->getType()
);