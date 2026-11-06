# Composites and field-level errors

When an object is made of several value objects, validate **each child** with its own `create()`, then aggregate.

## Pattern

```php
use Yadddl\ValueObject\Error\MultipleFieldErrorsBag;
use Yadddl\ValueObject\Validators\MultipleValidationContext;

readonly final class Person implements Failable
{
    use FailableSuccess;

    private function __construct(
        public string $name,
        public string $email,
        public int $age,
    ) {}

    public static function create(
        string $name,
        string $email,
        int $age,
    ): Person|MultipleFieldErrorsBag {
        $context = MultipleValidationContext::setup(
            name: Name::create($name),
            email: Email::create($email),
            age: Age::create($age),
        );

        return $context->isValid()
            ? new self(...$context->getValues())
            : $context->getErrors();
    }
}
```

Named arguments to `setup()` become **field names** in `MultipleFieldErrorsBag`.

Accepted successful results:

| Input | Stored in `getValues()` |
|-------|-------------------------|
| `ValidatedValue` | `$result->value` (primitive unwrap) |
| `BackedEnum` (e.g. from `BackedEnumValue::create`) | the **enum instance** |
| `ErrorsBag` | field errors (value omitted) |

```php
use Yadddl\ValueObject\Validators\BackedEnumValue;

$context = MultipleValidationContext::setup(
    status: BackedEnumValue::create(ListingStatus::class, $raw),
    name: Name::create($name),
);

// getValues()['status'] is ListingStatus::Published, not 'published'
```

## Handling field errors (e.g. HTTP / forms)

```php
$result = Person::create($name, $email, $age);

if ($result instanceof Person) {
    // persist $result
    return;
}

/** @var MultipleFieldErrorsBag $result */
foreach ($result->getErrorsByField() as $field => $errors) {
    foreach ($errors as $error) {
        $form->addError($field, $error->message);
    }
}
```

Helpers:

- `getErrorsForField('email')`
- `getFieldsWithErrors()`
- `getAllMessages()` (flat)
- `orFail()` — throws `ValidationFailedError` with `[field] message` formatting

## Nested graphs

Children can themselves be composites. Prefer:

1. Child `create()` → VO or error bag
2. Parent `MultipleValidationContext` or explicit merge
3. Serializer nested deserialize that calls each `create` (see [serializer.md](serializer.md))

The library does **not** implement DDD entity lifecycle (identity, repositories, mutability). Those stay in your domain code; YADDDL helps with validation composition and structural conversion.

## Important

- Parent must not re-check child invariants already enforced by child VOs
- Never hydrate a parent with `newInstanceWithoutConstructor` — always go through factories
