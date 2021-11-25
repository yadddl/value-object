[Home](../README.md)

# Create your own value object

Let's try to create a simple but composite data structure that describe and validate the domain concept of `Person`.

The Person should have a `Name`.

```php
class Name extends Text {
    protected string $regex = "/^[A-Za-z]{2,20}$/"; // Lenght min:2, max:20. No spaces
} 
```
Our value object is self validating, so cannot exist an instance of `Name` that doesn't follow the rules of our domain.
```php
$name = Name::create('John'); // it returns a Name

var_dump((string)$name); // string(4) "John"
```
This means that if we try to create an invalid name, we will have an `InvalidValueObject` instead of our instance.
```php
$wrongName = Name::create('Wrong name with spaces'); // it returns an InvalidValueObject

var_dump($wrongName->getMessage());  // string(81) "Invalid string: 'Wrong name with spaces' does not match with '/^[A-Za-z]{2,20}$/'"

var_dump($wrongName->getType()); // string(14) "invalid string"
```

Next step. One name is not enough, we need a `FullName` object with a `FirstName` and a `LastName`. 

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

Also, it's difficult to catch the errors and the code will crash while running cause a type error will rise. 

We need a better solution, like a factory method.

```php
class FullName  {
    private function __construct(
        private Name $firstName, 
        private Name $lastName
    ) {}
    
    public function getFirstName(): Name { return $this->firstName;}
    public function getLastName(): Name { return $this->lastName; }
    
    public static function create(string|Name $firstName, string|Name $lastName): static|ValidationError {
        return factory (
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
The `create` method will return or itself or, in case of invalid data, it will combine object all the errors generated in a `ValidationError`.



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

use Yadddl\DDD\Error\FailableTrait;

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

        $fullName = FullName::create(...$data, true)->orFail(); // Raise an exception if fail
        
        // DO stuff
      
        reutrn Response::json([], 200);
    } 
}
```

> NOTE: Request and Response class are fictional, not referred to something specific
