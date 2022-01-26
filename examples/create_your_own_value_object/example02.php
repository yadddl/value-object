<?php declare(strict_types=1);

require_once(__DIR__ . '/../../vendor/autoload.php');

use Yadddl\ValueObject\Primitive\Text;

// Value Objects Definition
class Name extends Text
{
    protected string $regex = "/^[A-Za-z]{2,20}$/"; // Length min:2, max:20. No spaces
}

class FullName
{
    public function __construct(
        private Name $firstName,
        private Name $lastName
    ) {
    }

    public function getFirstName(): Name
    {
        return $this->firstName;
    }

    public function getLastName(): Name
    {
        return $this->lastName;
    }
}

// A fatal error will be raised
$fullName = new FullName(
    new Name('John wrong name'),
    new Name('Smith wrong name')
);
