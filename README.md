# yadddl/value-object

Yet Another DDD Library — Value Objects with **functional validation** (PHP 8.5 pipes) and (upcoming) structural serialize/deserialize.

Requires **PHP 8.5+**.

## Principles

- If a value object instance exists, it is valid
- Validation returns `T|ErrorsBag` (union types) — no Either monad
- Errors accumulate; validation does not stop at the first failure
- Domain factories (`create`) own validation; serializers must not invent rules

## Quick start

```php
use Yadddl\ValueObject\Validators\IntegerValue;
use Yadddl\ValueObject\Error\ErrorsBag;

readonly final class Age
{
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

See `examples/` for `Age`, `Password`, `Person`, `Money`, and more.

## Structural serialize / deserialize

```php
use Yadddl\Serializer\StructuralConverter;
use Yadddl\ValueObject\Examples\Age;
use Yadddl\ValueObject\Examples\Person;

$converter = StructuralConverter::create();

$converter->toArray(Age::create(25)->orFail()); // 25 (ValidatedValue → scalar)
$converter->fromArray(25, Age::class);          // Age|ErrorsBag

$payload = $converter->toArray(
    Person::create('Jane', 'jane@example.com', 28)->orFail()
);
// ['name' => 'Jane', 'email' => 'jane@example.com', 'age' => 28]

$person = $converter->fromArray($payload, Person::class);
// Person|MultipleFieldErrorsBag — domain factories validate; serializer does not
```

Conventions:

- `ValidatedValue` → bare scalar
- public properties → associative array (recursive)
- enums → backing value / case name
- deserialize calls `create` / matching `create*` factories (never bypasses constructors)
- invalid input propagates `ErrorsBag` / `MultipleFieldErrorsBag` to the entry point

## Development

```bash
composer install
composer test
composer test-coverage   # requires Xdebug; enforces 100%
composer psalm
```

## Migration from 0.8 / standalone serializer

This is a **major** rewrite toward the functional PHP 8.5 model. Legacy constructor-throwing primitives, `factory()`/`Builder`, and Valinor are removed. Structural conversion now lives in this package under `Yadddl\Serializer` (replacing standalone `yadddl/serializer`).
