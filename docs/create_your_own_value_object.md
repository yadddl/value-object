[Home](../README.md)

# Create your own value object

Let's try to create a simple but composite data structure that describe and validate the concept of `Person` for an
example domain.

> **NOTE:** Describing a domain is not an easy task. There isn't a right or a wrong way, just depends on what you need.
> What comes next is just an example of what the library can do.

First bit. The Person should have a `Name`. For this domain, the name must have a length between 2 and 20 characters.

```php
class Name extends Text {
    protected string $regex = "/^[A-Za-z]{2,20}$/"; // Lenght min:2, max:20. No spaces
} 
```

Our value object is self validating, so cannot exist an instance of `Name` that doesn't follow the rules of our domain.

```php
$name = Name::create('John'); // it returns a Name

var_dump((string)$name); // string(4) "John"

echo "Hi {$name}, welcome!"; // Hi John, welcome!
```

If we try to create an invalid name, we will have an `InvalidValueObject` instance, instead of a `Name`.

```php
$wrongName = Name::create('Wrong name with spaces'); // it returns an InvalidValueObject

var_dump($wrongName->getMessage());  // string(81) "Invalid string: 'Wrong name with spaces' does not match with '/^[A-Za-z]{2,20}$/'"

var_dump($wrongName->getType()); // string(14) "invalid string"
```

Next step. One name is not enough, we also need a surname (we purposely want to keep it simple).

A `FullName` object with a `FirstName` and a `LastName` could do the job.

```php
class FullName  {
    public function __construct(
        private Name $firstName, 
        private Name $lastName
    ) {}
    
    public function getFirstName(): Name { return $this->firstName;}
    public function getLastName(): Name { return $this->lastName; }
}
```

And then we can instantiate it in this way.

```php
$fullName = new FullName (
    Name::create('John'),
    Name::create('Smith')
);
```

That's seems pretty decent, but if we want to hydrate the object, we had to do it manually, field by field.

giy Also, the application will crash due a `type error` because the constructor requires two `Name`; it doesn't expect
a `InvalidValueObject` error.

Here comes the helper `factory`. It will take all the Value Object and, if there's one or more `InvalidValueObject`, it
will return a `ValidationError`. You can think the `ValidationError` as a collection of `InvalidValueObject`.

```php
function factory(string $target, ...$args): mixed;
```

`$target` is the class that you want to create, `$args` the argument of the constructor, with the same order

```php
$fullName = factory (FullName::class,
    Name::create('John'),
    Name::create('Smith')
);
```
```php
class FullName  {
    private function __construct(
        private Name $firstName, 
        private Name $lastName
    ) {}
    
    public function getFirstName(): Name { return $this->firstName;}
    public function getLastName(): Name { return $this->lastName; }
    
    public static function create(string|Name $firstName, string|Name $lastName): static|ValidationError {
        return factory (  // <-- HERE
            FullName::class,
            Name::create($firstName),
            Name::create($lastName)
        );
    }
}
```

As you can see, the constructor is `private` and the arguments have the union type `string|Name` so we can pass both.

Now it's enough call the static method `create` and the `factory` helper will do the magic.

```php
$fullName = FullName::create('John', 'Smith');

/** OR **/

$data = ['firstName' => 'John', 'lastName' => 'Smith'];

$fullName = FullName::create(...$data);   
```

The `create` method will return or itself or, in case of invalid data, it will combine object all the errors generated
in a `ValidationError`.

```php
$fullName = FullName::create('John wrong name', 'Smith wrong name'); // It returns a ValidationError

print_r($fullName->getInvalidFields());

// Array
// (
//     [class] => FullName
//     [fields] => Array
//     (
//          [firstName] => Invalid string: 'John wrong name' does not match with '/^[A-Za-z]{2,20}$/'
//          [lastName] => Invalid string: 'Smith wrong name' does not match with '/^[A-Za-z]{2,20}$/'
//     )
// )


// No exception are raised untill now but,
// if you need to, you can throw
// the ValidationError object.
throw $fullName;
```

## Realistic example

```php
class ExampleController {
    public function __invoke (Request $request): Response {
        $data = $request->json();
        
        $fullName = FullName::create(...$data);
        
        if ($fullName instanceof ValidationError) {
            return Response::json($fullName->getInvalidFields(), 400);
        }
       
        // Else 
        // DO stuff
      
        reutrn Response::json([], 200);
    }
}
```

Or, if you manage your errors with a middleware or a centralized exception handler, we can use the trait `FailableTrait`

```php

use Yadddl\ValueObject\Error\FailableTrait;

class FullName  {
    use FailableTrait;
   
   /* implementation */
}
```

And then

```php
class ExampleController {
    public function __invoke (Request $request): Response {
        $data = $request->json();

        $fullName = FullName::create(...$data)->orFail(); // Raise an exception if fail
        
        // DO stuff
      
        reutrn Response::json([], 200);
    } 
}
```

> NOTE: Request and Response class are fictional, not referred to something specific
