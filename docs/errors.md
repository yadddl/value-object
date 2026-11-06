# Errors and `orFail()`

## Why unions instead of Either?

```php
public static function create(mixed $value): Age|ErrorsBag
```

- Native PHP types — IDEs understand `instanceof` narrowing
- No monadic API to learn for application code
- Clear intent at the signature

## Exception hierarchy (domain throws)

Validation still returns bags of typed `DomainError` instances. When something **throws**, failures share `DomainError`:

```text
\RuntimeException
└── Yadddl\ValueObject\Error\DomainError          (abstract)
    ├── InvalidField                              (abstract) — field / VO validation
    │   └── InvalidValue                          (concrete) — generic message-only field error
    │   └── … project types (e.g. InvalidEmail)
    ├── InvalidEntity                             (abstract) — entity / aggregate invariants
    │   └── … project types (e.g. AccountCannotBeApproved)
    └── ValidationFailedError                     (concrete) — orFail() bag container
```

| Type | Typical meaning | Typical HTTP |
|------|-----------------|--------------|
| `InvalidField` (and subclasses) | Invalid input for a field/VO | 422 |
| `InvalidEntity` (and subclasses) | Valid values, illegal operation/state | 409 / 422 |
| `ValidationFailedError` | Multi-error bag forced via `orFail()` (`getErrors()`) | 422 |
| Other `\Throwable` (DB, bugs, `DeserializationError`, …) | Infra / unexpected | 500 |

`DeserializationError` (serializer) stays infrastructure — not under `DomainError`.

```php
try {
    $order = $handler->handle($command);
} catch (ValidationFailedError $e) {
    return new JsonResponse(['errors' => $e->getErrors()], 422);
} catch (DomainError $e) {
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
    $result->count();              // number of DomainError
    $result->getMessages();        // list<string>
    $result->getErrors();          // list<DomainError>
    (string) $result;              // "msg1; msg2"
    $result->orFail();             // never — throws ValidationFailedError
}
```

Factories:

- `ErrorsBag::empty()`
- `ErrorsBag::fromMessages('a', 'b')` — wraps each message in `InvalidValue`
- `ErrorsBag::fromError(new InvalidValue('…'))` — or any `DomainError`

## `MultipleFieldErrorsBag` (composites)

```php
$result = Person::create('', 'bad', -1);

if ($result instanceof MultipleFieldErrorsBag) {
    $result->getErrorsByField();     // field => DomainError[]
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
- On an **error bag**: `orFail(): never` throws `ValidationFailedError` (a `DomainError` container)

So this is safe when the union is still un-narrowed:

```php
$age = Age::create($input)->orFail(); // Age, or ValidationFailedError
```

Use `orFail()` at **boundaries** (console commands, jobs) where failure should abort. Prefer `instanceof` in HTTP form flows so you can render every message without catching. Prefer throwing a **project-specific** `InvalidField` / `InvalidEntity` from use cases when a single typed failure is enough.

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
