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

## Development

```bash
composer install
composer test
composer test-coverage   # requires Xdebug; enforces 100%
composer psalm
```

## Migration from 0.8

This is a **major** rewrite toward the functional PHP 8.5 model. Legacy constructor-throwing primitives, `factory()`/`Builder`, and Valinor are removed. Details will land in the changelog before `1.0.0`.
