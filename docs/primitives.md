# Library primitives & date composites

Ready-made types built on the functional validators. Use them as-is, or subclass where noted.

## Text family

### `Text`

- Wraps `string` via `ValidatedValue`
- Default regex `REGEX = '/(.+)/'` (at least one character in a capturing group)
- `toUpperCase()` / `toLowerCase()` return new instances
- `equals()`, `__toString()`

```php
$text = Text::create('Hello')->orFail();
$text->toLowerCase()->value; // "hello"
```

### `NotEmptyText`

Extends `Text` with `REGEX = '/.+/`.

### `Email`

```php
Email::create('user@example.com'); // Email|ErrorsBag
```

Uses `StringValue::email()`.

### Custom text types

```php
readonly class CountryCode extends Text
{
    protected const REGEX = '/^[A-Z]{2}$/';
}
```

---

## Integer family

### `Integer`

- `MIN = PHP_INT_MIN`, `MAX = PHP_INT_MAX` by default
- `create(mixed): static|ErrorsBag`
- `equals()`, `__toString()`

### `PositiveInteger`

Extends `Integer` with `MIN = 0` (zero allowed).

### Custom ranges

```php
readonly class Percentage extends Integer
{
    protected const MIN = 0;
    protected const MAX = 100;
}
```

---

## Date composites

Namespace `Yadddl\ValueObject\Composite`.

### `Date`

```php
Date::create('2024-01-15');           // from Y-m-d string
Date::createFrom(15, 1, 2024);        // day, month, year
Date::create($dateTimeImmutable);
Date::now();

$date->format('d/m/Y');
$date->toDateTimeImmutable();
$date->equals($other);
(string) $date; // 2024-01-15
```

Invalid calendar dates (e.g. `2024-02-30`) return `ErrorsBag` (strict round-trip parsing).

### `Time`

```php
Time::create('08:05:09');
Time::createFrom(8, 5, 9);
Time::now();
```

### `DateTime`

```php
DateTime::create('2024-01-15 08:05:09');
DateTime::createFromDateAndTime($date, $time);
DateTime::now();

$dateTime->format('Y-m-d H:i');
$dateTime->toDateTimeImmutable();
```

---

## Serialization tip

- `Text` / `Integer` / `Email` implement `ValidatedValue` → serializer emits **scalars**
- `Date` / `Time` / `DateTime` expose multiple public properties by default; use `StructuralConverter::create()` to emit **strings** for natural `create(string)` round-trips

See [serializer.md](serializer.md).
