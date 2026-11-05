# Migration from 0.8 (and from `yadddl/serializer` 0.6)

This is a **major** release. Prefer adapting call sites over shims.

## Requirements

| Before | After |
|--------|--------|
| PHP `>=8.2` | PHP `^8.5` |
| Optional unused `cuyz/valinor` | No Valinor |
| Separate `yadddl/serializer` | Use `Yadddl\Serializer\*` in this package |

```bash
composer require yadddl/value-object:^1.0
composer remove yadddl/serializer cuyz/valinor   # if present
```

## Value objects

### Before (0.8)

```php
// Often: public constructor that throws
new Age(-1); // InvalidArgumentException / ValueError

// Or incomplete create() + factory() hydration
$result = factory(Person::class, $array); // object|ValidationError
```

### After (1.0)

```php
$result = Age::create(-1); // Age|ErrorsBag

if ($result instanceof Age) {
    // valid
} else {
    foreach ($result->getMessages() as $message) {
        // show all accumulated errors
    }
}

// When exceptions are desired at the boundary:
$age = Age::create($input)->orFail(); // throws ValidationFailedException (DomainException)
```

### Checklist

1. Make constructors **private**; expose `create(...): static|ErrorsBag`
2. Replace inline `or throw` checks with pipe validators (`IntegerValue`, `StringValue`, …)
3. Replace `ValidationError` / `InvalidValueObject` handling with `instanceof ErrorsBag` / `MultipleFieldErrorsBag`
4. Remove `factory()` / `Builder` usage
5. For nested objects from arrays, use `StructuralConverter::fromArray()` (calls `create`)
6. Subclass hooks: `Text::REGEX`, `Integer::MIN` / `MAX` still work, but validation is pipe-based

### Composites / “entities”

```php
// Aggregate child create() results — errors by field
$context = MultipleValidationContext::setup(
    name: Name::create($name),
    email: Email::create($email),
    age: Age::create($age),
);

return $context->isValid()
    ? new Person(...$context->getValues())
    : $context->getErrors(); // MultipleFieldErrorsBag
```

## Serializer consumers (`yadddl/serializer` 0.6)

| Before | After |
|--------|--------|
| `yadddl/serializer` package | `yadddl/value-object` |
| Callable `$serializer($object)` | `$converter->toArray($object)` |
| No deserialize | `$converter->fromArray($data, Type::class)` |
| Symfony PropertyAccess getters | Public / promoted properties (YADDDL style) |
| Silent `__toString` for many objects | Prefer property graph; Date/Time/DateTime as strings via `StructuralConverter::create()` |

```php
use Yadddl\Serializer\StructuralConverter;

$converter = StructuralConverter::create();

$array = $converter->toArray($person);
$person = $converter->fromArray($array, Person::class);
// Person|MultipleFieldErrorsBag — do not assume success
```

## Removed symbols (non-exhaustive)

- `Yadddl\ValueObject\factory`
- `Yadddl\ValueObject\Factory\Builder`, `BuilderImpl`
- `Yadddl\ValueObject\Error\ValidationError`, `InvalidValueObject`, `FieldError`, …
- `Yadddl\ValueObject\Attributes\Collection`
- Old public throwing constructors on primitives as the supported API

## Suggested rollout

1. Upgrade PHP to 8.5
2. Migrate one aggregate (e.g. `Person`) end-to-end: `create` + serializer round-trip + tests
3. Replace remaining primitives with library `Text` / `Integer` / `Email` / dates or your own pipe-based VOs
4. Delete dead `factory()` call sites
5. Point Packagist / apps at `^1.0`
