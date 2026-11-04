# Getting started

## Install

```bash
composer require yadddl/value-object
```

Requires **PHP 8.5+** (pipe operator).

## Mental model

1. **Self-validating** — if you hold an instance, it is valid
2. **Factories return unions** — `Age|ErrorsBag`, never an invalid object
3. **Accumulate errors** — pipes keep going; you see every failed rule
4. **Serializer does not validate** — it only moves structure and calls `create`

## Your first value object

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

### Using it

```php
$result = Age::create($_POST['age'] ?? null);

if ($result instanceof Age) {
    echo $result->value;
} elseif ($result instanceof ErrorsBag) {
    foreach ($result->getMessages() as $message) {
        echo $message, PHP_EOL;
    }
}

// At a trusted boundary (CLI script, already-validated admin tool):
$age = Age::create(25)->orFail();
```

## Ready-made primitives

When you do not need a custom type:

```php
use Yadddl\ValueObject\Primitive\Email;
use Yadddl\ValueObject\Primitive\PositiveInteger;
use Yadddl\ValueObject\Primitive\NotEmptyText;

$email = Email::create('user@example.com');
$qty = PositiveInteger::create(3);
$name = NotEmptyText::create('Ada');
```

See [primitives.md](primitives.md).

## Persist / load as arrays

```php
use Yadddl\Serializer\StructuralConverter;

$converter = StructuralConverter::create();

$payload = $converter->toArray($age);     // 25
$restored = $converter->fromArray(25, Age::class); // Age|ErrorsBag
```

See [serializer.md](serializer.md).

## Next

- [Build value objects](value-objects.md)
- [Composites and field errors](composites.md)
- [Validator reference](validators.md)
- [Error handling](errors.md)
- [Migration from 0.8](migration-from-0.8.md)
