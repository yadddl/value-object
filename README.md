# yadddl/value-object

**Yet Another DDD Library** — reusable PHP value objects with **functional validation** (PHP 8.5 pipes) and **structural serialize/deserialize** in one package.

Requires **PHP 8.5+**.

```bash
composer require yadddl/value-object
```

## Why

- If a value object **instance exists, it is valid**
- Validation returns **`T|ErrorsBag`** (union types — no Either monad)
- Errors **accumulate**; pipelines do not stop at the first failure
- The **serializer does not validate** — it calls domain `create()` factories
- No application-framework dependencies

Inspired by the [Value Objects in PHP 8 series](https://dev.to/cnastasi/series/25573), especially the [functional approach](https://dev.to/cnastasi/value-objects-in-php-8-lets-introduce-a-functional-approach-3aan).

## Quick start

### Define a value object

```php
use Yadddl\ValueObject\Contracts\ValidatedValue;
use Yadddl\ValueObject\Error\ErrorsBag;
use Yadddl\ValueObject\Error\Failable;
use Yadddl\ValueObject\Error\FailableSuccess;
use Yadddl\ValueObject\Validators\IntegerValue;

readonly final class Age implements Failable, ValidatedValue
{
    use FailableSuccess;

    private function __construct(public int $value) {}

    public static function create(mixed $value): Age|ErrorsBag
    {
        $context = $value
            |> IntegerValue::from(...)
            |> IntegerValue::min(0, 'Age cannot be negative')
            |> IntegerValue::max(150, 'Age cannot exceed 150');

        return $context->isValid()
            ? new self($context->getValue())
            : $context->getErrors();
    }
}
```

### Use it

```php
$result = Age::create($_POST['age'] ?? null);

if ($result instanceof Age) {
    echo $result->value;
} else {
    foreach ($result->getMessages() as $message) {
        echo $message, PHP_EOL;
    }
}

// Trusted boundary:
$age = Age::create(25)->orFail();
```

### Composite with field errors

```php
$context = MultipleValidationContext::setup(
    name: Name::create($name),
    email: Email::create($email),
    age: Age::create($age),
);

return $context->isValid()
    ? new Person(...$context->getValues())
    : $context->getErrors(); // MultipleFieldErrorsBag
```

### Serialize / deserialize

```php
use Yadddl\Serializer\StructuralConverter;

$converter = StructuralConverter::create();

$converter->toArray($age);                      // 25
$converter->fromArray(25, Age::class);          // Age|ErrorsBag

$payload = $converter->toArray($person);
// ['name' => …, 'email' => …, 'age' => …]

$person = $converter->fromArray($payload, Person::class);
// Person|MultipleFieldErrorsBag
```

## Documentation

| Guide | Contents |
|-------|----------|
| [Getting started](docs/getting-started.md) | Install, first VO, primitives, converter |
| [Building value objects](docs/value-objects.md) | Canonical shape, password-like rules, examples |
| [Composites](docs/composites.md) | `MultipleValidationContext`, form errors |
| [Validators](docs/validators.md) | `IntegerValue` / `StringValue` / `ArrayValue` API |
| [Errors](docs/errors.md) | `ErrorsBag`, `orFail()`, entry-point mapping |
| [Primitives & dates](docs/primitives.md) | `Text`, `Integer`, `Email`, `Date`/`Time`/`DateTime` |
| [Serializer](docs/serializer.md) | Conventions, round-trips, nested graphs |
| [Migration from 0.8](docs/migration-from-0.8.md) | Breaking changes from YAVOL / YASL |
| [Changelog](CHANGELOG.md) | Release notes |

Runnable samples live under [`examples/`](examples/).

## Package layout

```text
Yadddl\ValueObject\…   validators, errors, primitives, composites
Yadddl\Serializer\…    structural object ↔ array (same Composer package)
```

Domain code should depend on `Yadddl\ValueObject` only. Adapters may also use `Yadddl\Serializer`.

## Development

```bash
composer install
composer test
composer test-coverage   # Xdebug; enforces 100% coverage
composer psalm
composer cs-check
```

## License

MIT — see [LICENSE](LICENSE).
