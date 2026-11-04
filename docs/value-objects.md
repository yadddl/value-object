# Building value objects

## Canonical shape

```php
readonly final class Price implements Failable, ValidatedValue
{
    use FailableSuccess;

    private function __construct(public int $value) {} // cents

    public static function create(mixed $value): Price|ErrorsBag
    {
        $context = self::validate($value);

        return $context->isValid()
            ? new self($context->getValue())
            : $context->getErrors();
    }

    public static function validate(mixed $value): IntegerValue
    {
        return $value
            |> IntegerValue::from(...)
            |> IntegerValue::min(0, 'Price cannot be negative')
            |> IntegerValue::max(100_000_00, 'Price is too large');
    }
}
```

### Rules of thumb

| Do | Don't |
|----|--------|
| Private constructor | `new Price(-1)` from outside |
| `create(): static\|ErrorsBag` | Throw for normal validation failures |
| Pipe reusable validators | Copy-paste `if` chains in every VO |
| Implement `ValidatedValue` for scalar wrappers | Nest `{ "value": … }` unless you mean it |
| Use `Failable` + `FailableSuccess` for `orFail()` | Catch exceptions as the happy path |

## Multi-rule strings (password-like)

All failing rules are collected:

```php
$context = $value
    |> StringValue::from(...)
    |> StringValue::minLength(8, 'Too short')
    |> StringValue::maxLength(20, 'Too long')
    |> StringValue::hasUppercase('Need uppercase')
    |> StringValue::hasLowercase('Need lowercase')
    |> StringValue::hasNumber('Need a digit')
    |> StringValue::hasSpecialCharacter('Need a special character');
```

See `examples/Password.php` for a full VO that also hashes on success.

## Custom messages

Every validator accepts an optional message so the same rule reads differently per domain:

```php
IntegerValue::min(0, 'Age cannot be negative');
IntegerValue::min(0, 'Quantity cannot be negative');
```

## Extract `validate()` for tests

```php
public static function validate(mixed $value): IntegerValue { … }

// in tests
expect(Age::validate(-1)->isValid())->toBeFalse();
```

## Equality

Scalar primitives expose `equals()`:

```php
Text::create('a')->orFail()->equals(Text::create('a')->orFail()); // true
```

Prefer comparing values in the domain language (`$left->equals($right)`) over comparing public properties ad hoc when types matter.

## Full examples in the repo

| File | Shows |
|------|--------|
| `examples/Age.php` | Integer range VO |
| `examples/Name.php` | String length VO |
| `examples/Email.php` | Email format |
| `examples/Password.php` | Many rules + side effect on success |
| `examples/Money.php` | Parse string → structured VO with `ArrayValue` |
| `examples/Person.php` | Composite with field-level errors |
