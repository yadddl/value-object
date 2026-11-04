# Errors and `orFail()`

## Why unions instead of Either?

```php
public static function create(mixed $value): Age|ErrorsBag
```

- Native PHP types — IDEs understand `instanceof` narrowing
- No monadic API to learn for application code
- Clear intent at the signature

## `ErrorsBag` (single VO / flat list)

```php
$result = Age::create(-5);

if ($result instanceof ErrorsBag) {
    $result->hasErrors();          // true
    $result->count();              // number of Error
    $result->getMessages();        // list<string>
    $result->getErrors();          // list<Error>
    (string) $result;              // "msg1; msg2"
    $result->orFail();             // never — throws InvalidArgumentException
}
```

Factories:

- `ErrorsBag::empty()`
- `ErrorsBag::fromMessages('a', 'b')`
- `ErrorsBag::fromError(new Error('…'))`

## `MultipleFieldErrorsBag` (composites)

```php
$result = Person::create('', 'bad', -1);

if ($result instanceof MultipleFieldErrorsBag) {
    $result->getErrorsByField();     // field => Error[]
    $result->getErrorsForField('age');
    $result->getFieldsWithErrors();  // ['name', 'email', 'age']
    $result->getAllMessages();
    (string) $result;                // "[name] …; [email] …"
    $result->orFail();
}
```

## `Failable` / `FailableSuccess`

```php
interface Failable
{
    public function orFail(): static;
}
```

- On a **successful VO**: `orFail()` returns `$this` (`FailableSuccess` trait)
- On an **error bag**: `orFail(): never` throws

So this is safe when the union is still un-narrowed:

```php
$age = Age::create($input)->orFail(); // Age, or exception
```

Use `orFail()` at **boundaries** (console commands, jobs) where failure should abort. Prefer `instanceof` in HTTP form flows so you can render every message.

## Entry point mapping (recommended)

```php
$result = $converter->fromArray($payload, Person::class);

return match (true) {
    $result instanceof Person => new JsonResponse($result, 201),
    $result instanceof MultipleFieldErrorsBag => new JsonResponse([
        'errors' => $result->getErrorsByField(), // map to your API shape
    ], 422),
    $result instanceof ErrorsBag => new JsonResponse([
        'errors' => $result->getMessages(),
    ], 422),
};
```

The serializer never decides how to render — your entry point does.
