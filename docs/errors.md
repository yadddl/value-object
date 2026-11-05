# Errors and `orFail()`

## Why unions instead of Either?

```php
public static function create(mixed $value): Age|ErrorsBag
```

- Native PHP types — IDEs understand `instanceof` narrowing
- No monadic API to learn for application code
- Clear intent at the signature

## Exception hierarchy (boundary throws)

Validation still returns bags. When something **throws**, domain failures share a parent so entry points can classify them:

```text
\DomainException
└── Yadddl\ValueObject\Error\DomainException
    ├── ValidationFailedException   ← orFail() on ErrorsBag / MultipleFieldErrorsBag
    └── DomainLogicException        ← invariants / illegal state (subclass in your app)
```

| Type | Typical meaning | Typical HTTP |
|------|-----------------|--------------|
| `ValidationFailedException` | Invalid input (bag available via `getErrors()`) | 422 |
| `DomainLogicException` | Valid values, illegal operation/state | 409 / 422 |
| Other `\Throwable` (DB, bugs, `DeserializationError`, …) | Infra / unexpected | 500 |

`DeserializationError` (serializer) stays infrastructure — not under `DomainException`.

```php
try {
    $order = $handler->handle($command);
} catch (ValidationFailedException $e) {
    return new JsonResponse(['errors' => $e->getErrors()], 422);
} catch (DomainException $e) {
    return new JsonResponse(['error' => $e->getMessage()], 409);
} catch (Throwable $e) {
    throw $e; // or map to 500
}
```

## `ErrorsBag` (single VO / flat list)

```php
$result = Age::create(-5);

if ($result instanceof ErrorsBag) {
    $result->hasErrors();          // true
    $result->count();              // number of Error
    $result->getMessages();        // list<string>
    $result->getErrors();          // list<Error>
    (string) $result;              // "msg1; msg2"
    $result->orFail();             // never — throws ValidationFailedException
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
- On an **error bag**: `orFail(): never` throws `ValidationFailedException` (a `DomainException`)

So this is safe when the union is still un-narrowed:

```php
$age = Age::create($input)->orFail(); // Age, or ValidationFailedException
```

Use `orFail()` at **boundaries** (console commands, jobs) where failure should abort. Prefer `instanceof` in HTTP form flows so you can render every message without catching.

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
