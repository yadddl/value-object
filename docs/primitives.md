# Library primitives & date composites

Ready-made types built on the functional validators. Use them as-is, extend them with hooks (`REGEX`, `MIN` / `MAX`), or copy the pattern for your own domain types.

Every type below:

- is created with `create(...)` (or a dedicated factory) returning `static|ErrorsBag`
- never exposes an invalid instance
- supports `orFail()` via `Failable` where implemented

---

## Index

1. [Summary table](#summary-table)
2. [Text family](#text-family)
   - [Text](#text)
   - [NotEmptyText](#notemptytext)
   - [Email](#email)
   - [Url](#url)
   - [Uuid](#uuid)
   - [Custom text types](#custom-text-types)
3. [Integer family](#integer-family)
   - [Integer](#integer)
   - [PositiveInteger](#positiveinteger)
   - [StrictlyPositiveInteger](#strictlypositiveinteger)
   - [Custom ranges](#custom-ranges)
4. [Date composites](#date-composites)
   - [Date](#date)
   - [Time](#time)
   - [DateTime](#datetime)
   - [DateTimeWithTimeZone](#datetimewithtimezone)
   - [TimeZone](#timezone)
5. [Serialization cheat sheet](#serialization-cheat-sheet)
6. [Choosing a type](#choosing-a-type)

---

## Summary table

### Scalar primitives (`Yadddl\ValueObject\Primitive`)

| Type | PHP value | Constraint (default) | Extensibility | ValidatedValue | Useful helpers |
|------|-----------|----------------------|---------------|----------------|----------------|
| [Text](#text) | `string` | Matches regex `/(.+)/` | Override `REGEX` in a subclass | Yes → serialize as scalar | `toUpperCase()`, `toLowerCase()`, `equals()`, `__toString()` |
| [NotEmptyText](#notemptytext) | `string` | Non-empty (`/.+/`) | Same as Text | Yes | Inherits Text helpers |
| [Email](#email) | `string` | Valid email (`FILTER_VALIDATE_EMAIL`) | Prefer a custom VO if you need extra rules | Yes | `equals()`, `__toString()` |
| [Url](#url) | `string` | Valid URL (`FILTER_VALIDATE_URL`) | Prefer a custom VO for scheme/host rules | Yes | `equals()`, `__toString()` |
| [Uuid](#uuid) | `string` | Valid UUID via [ramsey/uuid](https://github.com/ramsey/uuid); stored lowercase | Prefer a custom VO for version/variant constraints | Yes | `generate()`, `fromRamsey()`, `toRamsey()`, `equals()`, `__toString()` |
| [Integer](#integer) | `int` | Full `PHP_INT_MIN` … `PHP_INT_MAX` range | Override `MIN` / `MAX` | Yes | `equals()`, `__toString()` |
| [PositiveInteger](#positiveinteger) | `int` | `MIN = 0`, `MAX = PHP_INT_MAX` | Same as Integer | Yes | Inherits Integer helpers |
| [StrictlyPositiveInteger](#strictlypositiveinteger) | `int` | `MIN = 1`, `MAX = PHP_INT_MAX` | Same as Integer | Yes | Inherits Integer helpers |
| [TimeZone](#timezone) | `string` | Valid PHP timezone id / offset (`DateTimeZone`) | Prefer a custom VO for allow-lists | Yes | `utc()`, `fromDateTimeZone()`, `toDateTimeZone()`, `equals()`, `__toString()` |

### Date composites (`Yadddl\ValueObject\Composite`)

| Type | Shape | Main factories | Notes | Serialize (default / `StructuralConverter::create()`) |
|------|-------|----------------|-------|------------------------------------------------------|
| [Date](#date) | `days`, `months`, `years` | `create`, `createFrom`, `fromString`, `createFromFormat`, `now` | Strict calendar check (rejects 2024-02-30) | props array / `Y-m-d` string |
| [Time](#time) | `hours`, `minutes`, `seconds` | `create`, `createFrom`, `fromString`, `now` | `HH:MM:SS` | props array / `H:i:s` string |
| [DateTime](#datetime) | nested Date + Time | `create`, `createFromString`, `createFromFormat`, `createFromDateAndTime`, `now` | Naive / no zone; format `Y-m-d H:i:s` | nested props / datetime string |
| [DateTimeWithTimeZone](#datetimewithtimezone) | Date + Time + [TimeZone](#timezone) | `create`, `createFromString`, `createFromFormat`, `createFromDateTimeAndTimeZone`, `now` | Format `Y-m-d H:i:s e` | nested props / zoned string |
---

## Text family

### Text

General-purpose string wrapper.

| | |
|--|--|
| Namespace | `Yadddl\ValueObject\Primitive\Text` |
| Create | `Text::create(mixed $value): Text\|ErrorsBag` |
| Stored as | `public string $value` |
| Default rule | `REGEX = '/(.+)/'` — at least one character (capturing group) |

```php
use Yadddl\ValueObject\Primitive\Text;

$text = Text::create('Hello')->orFail();

$text->value;                 // "Hello"
(string) $text;               // "Hello"
$text->toLowerCase()->value;  // "hello"
$text->toUpperCase()->value;  // "HELLO"
$text->equals(Text::create('Hello')->orFail()); // true
```

Empty string fails validation with the default regex.

### NotEmptyText

Thin specialization of `Text`:

| | |
|--|--|
| Extends | `Text` |
| Default rule | `REGEX = '/.+/'` (non-empty) |

```php
use Yadddl\ValueObject\Primitive\NotEmptyText;

NotEmptyText::create('');     // ErrorsBag
NotEmptyText::create('Ada');  // NotEmptyText
```

### Email

Dedicated email VO (does not extend `Text`).

| | |
|--|--|
| Create | `Email::create(mixed $value): Email\|ErrorsBag` |
| Rule | `StringValue::email()` |

```php
use Yadddl\ValueObject\Primitive\Email;

Email::create('user@example.com'); // Email|ErrorsBag
Email::create('not-an-email');     // ErrorsBag
```

### Url

Dedicated URL VO (does not extend `Text`).

| | |
|--|--|
| Create | `Url::create(mixed $value): Url\|ErrorsBag` |
| Rule | `StringValue::url()` (`FILTER_VALIDATE_URL`) |

```php
use Yadddl\ValueObject\Primitive\Url;

Url::create('https://example.com/path'); // Url|ErrorsBag
Url::create('not-a-url');                // ErrorsBag
```

### Uuid

UUID string VO backed by [ramsey/uuid](https://github.com/ramsey/uuid). Values are stored **lowercase**.

| | |
|--|--|
| Create | `Uuid::create(mixed $value): Uuid\|ErrorsBag` |
| Generate | `Uuid::generate(): Uuid` (random v4) |
| Ramsey | `Uuid::fromRamsey(UuidInterface): Uuid`, `$uuid->toRamsey(): UuidInterface` |

```php
use Yadddl\ValueObject\Primitive\Uuid;

$uuid = Uuid::create('550E8400-E29B-41D4-A716-446655440000')->orFail();
$uuid->value; // "550e8400-e29b-41d4-a716-446655440000"

$fresh = Uuid::generate();
$ramsey = $fresh->toRamsey();
```

### Custom text types

Subclass `Text` and override `REGEX`:

```php
use Yadddl\ValueObject\Primitive\Text;

readonly class CountryCode extends Text
{
    protected const REGEX = '/^[A-Z]{2}$/';
}

CountryCode::create('IT'); // CountryCode
CountryCode::create('it'); // ErrorsBag
```

---

## Integer family

### Integer

| | |
|--|--|
| Create | `Integer::create(mixed $value): Integer\|ErrorsBag` |
| Stored as | `public int $value` |
| Default range | `MIN = PHP_INT_MIN`, `MAX = PHP_INT_MAX` |

```php
use Yadddl\ValueObject\Primitive\Integer;

$n = Integer::create(42)->orFail();
$n->value;     // 42
(string) $n;   // "42"
```

Non-integers (e.g. `"42"`) fail at the `IntegerValue::from` step — no silent coercion.

### PositiveInteger

| | |
|--|--|
| Extends | `Integer` |
| Range | `MIN = 0` (zero **allowed**), `MAX = PHP_INT_MAX` |

```php
use Yadddl\ValueObject\Primitive\PositiveInteger;

PositiveInteger::create(0);   // ok
PositiveInteger::create(10);  // ok
PositiveInteger::create(-1);  // ErrorsBag
```

### StrictlyPositiveInteger

| | |
|--|--|
| Extends | `Integer` |
| Range | `MIN = 1` (zero **rejected**), `MAX = PHP_INT_MAX` |

```php
use Yadddl\ValueObject\Primitive\StrictlyPositiveInteger;

StrictlyPositiveInteger::create(1);  // ok
StrictlyPositiveInteger::create(0);  // ErrorsBag
StrictlyPositiveInteger::create(-1); // ErrorsBag
```

### Custom ranges

```php
use Yadddl\ValueObject\Primitive\Integer;

readonly class Percentage extends Integer
{
    protected const MIN = 0;
    protected const MAX = 100;
}

Percentage::create(50);  // Percentage
Percentage::create(101); // ErrorsBag
```

---

## Date composites

Namespace: `Yadddl\ValueObject\Composite`.

These are **not** `ValidatedValue` scalars: they expose structured public properties (or nest other composites). Prefer string serialization via `StructuralConverter::create()` when round-tripping through `create(string)`.

### Date

Calendar date (`Y-m-d`).

| Factory | Input | Result |
|---------|-------|--------|
| `create(string\|Date\|DateTimeInterface)` | string / self / native datetime | `Date\|ErrorsBag` |
| `createFrom(int $days, int $months, int $years)` | parts | `Date\|ErrorsBag` |
| `fromString` / `createFromFormat` | formatted string | `Date\|ErrorsBag` |
| `now()` | — | `Date` (always valid “today”) |
| `createFromDateTimeInterface` | `DateTimeInterface` | `Date` |

```php
use Yadddl\ValueObject\Composite\Date;

$date = Date::create('2024-01-15')->orFail();

$date->years;   // 2024
$date->months;  // 1
$date->days;    // 15
(string) $date; // "2024-01-15"
$date->format('d/m/Y');
$date->toDateTimeImmutable();
$date->equals(Date::createFrom(15, 1, 2024)->orFail());

Date::create('2024-02-30'); // ErrorsBag — strict calendar
```

### Time

Clock time (`H:i:s`).

| Factory | Input |
|---------|-------|
| `create` / `fromString` | `"08:05:09"` |
| `createFrom(int $hours, int $minutes, int $seconds)` | parts |
| `now()` / `createFromDateTimeInterface` | clock / native |

```php
use Yadddl\ValueObject\Composite\Time;

$time = Time::create('08:05:09')->orFail();
(string) $time; // "08:05:09"
$time->toInt(); // 80509
```

### DateTime

Pair of `Date` + `Time`.

| Factory | Input |
|---------|-------|
| `create(string\|DateTimeInterface\|DateTime)` | `"Y-m-d H:i:s"` / native / self |
| `createFromDateAndTime(Date, Time)` | nested VOs |
| `createFromString` / `createFromFormat` | formatted string |
| `now()` | current instant |

```php
use Yadddl\ValueObject\Composite\Date;
use Yadddl\ValueObject\Composite\DateTime;
use Yadddl\ValueObject\Composite\Time;

$dt = DateTime::create('2024-01-15 08:05:09')->orFail();
$dt = DateTime::createFromDateAndTime(
    Date::create('2024-01-15')->orFail(),
    Time::create('08:05:09')->orFail(),
);

(string) $dt; // "2024-01-15 08:05:09"
$dt->format('Y-m-d H:i');
$dt->toDateTimeImmutable();
```

Use this when the wall-clock value has **no** timezone meaning (or the zone lives elsewhere). Prefer [DateTimeWithTimeZone](#datetimewithtimezone) when the instant must carry a zone.

### DateTimeWithTimeZone

`Date` + `Time` + [`TimeZone`](#timezone). Default string form: `Y-m-d H:i:s e`.

| Factory | Input |
|---------|-------|
| `create(string\|DateTimeInterface\|DateTimeWithTimeZone)` | `"2024-01-15 08:05:09 Europe/Rome"` / native / self |
| `createFromDateTimeAndTimeZone(Date, Time, TimeZone)` | nested VOs |
| `createFromString` / `createFromFormat` | formatted string |
| `now(string\|\DateTimeZone\|TimeZone\|null)` | current instant in the given (or default) zone; returns `ErrorsBag` if the zone is invalid |

```php
use Yadddl\ValueObject\Composite\DateTimeWithTimeZone;
use Yadddl\ValueObject\Primitive\TimeZone;

$dt = DateTimeWithTimeZone::create('2024-01-15 08:05:09 Europe/Rome')->orFail();

$dt->timeZone->value;           // "Europe/Rome"
(string) $dt;                   // "2024-01-15 08:05:09 Europe/Rome"
$dt->toDateTimeImmutable();     // zone preserved
$dt->withoutTimeZone();         // naive DateTime (same date + time)
DateTimeWithTimeZone::now(TimeZone::utc());
```

### TimeZone

Validated PHP timezone identifier (or offset accepted by `DateTimeZone`). Lives in `Yadddl\ValueObject\Primitive`.

| | |
|--|--|
| Create | `TimeZone::create(mixed $value): TimeZone\|ErrorsBag` |
| Helpers | `utc()`, `fromDateTimeZone()`, `toDateTimeZone()` |

```php
use Yadddl\ValueObject\Primitive\TimeZone;

TimeZone::create('Europe/Rome'); // TimeZone|ErrorsBag
TimeZone::create('Not/AZone');   // ErrorsBag
TimeZone::utc()->value;          // "UTC"
```

---

## Serialization cheat sheet

| Type | Default `StructuralSerializer` | With `StructuralConverter::create()` |
|------|--------------------------------|--------------------------------------|
| `Text`, `NotEmptyText`, `Email`, `Url`, `Uuid`, `Integer`, `PositiveInteger`, `StrictlyPositiveInteger`, `TimeZone` | **scalar** (`$value`) | same |
| `Date` | `{days, months, years}` → `createFrom` | `"Y-m-d"` → `create(string)` |
| `Time` | `{hours, minutes, seconds}` → `createFrom` | `"H:i:s"` → `create(string)` |
| `DateTime` | `{date: {…}, time: {…}}` → `createFromDateAndTime` | `"Y-m-d H:i:s"` → `create(string)` |
| `DateTimeWithTimeZone` | `{date, time, timeZone}` → `createFromDateTimeAndTimeZone` | `"Y-m-d H:i:s e"` → `create(string)` |

Details: [serializer.md](serializer.md).

---

## Choosing a type

| Need | Prefer |
|------|--------|
| Free-form non-empty string | `NotEmptyText` or `Text` |
| Email address | `Email` |
| HTTP(S) / absolute URL | `Url` |
| Identifier UUID | `Uuid` (`generate()` for new ids) |
| Domain-specific string format | Subclass `Text` + `REGEX` |
| Any int in PHP range | `Integer` |
| Non-negative count / amount (zero ok) | `PositiveInteger` |
| Positive count / id (zero rejected) | `StrictlyPositiveInteger` |
| Bounded int (%, score, …) | Subclass `Integer` + `MIN`/`MAX` |
| Calendar / clock values (naive) | `Date`, `Time`, `DateTime` |
| Instant with timezone | `DateTimeWithTimeZone` (+ `TimeZone`) |
| Richer domain meaning (`Age`, `Money`, …) | Custom VO in your app (see [value-objects.md](value-objects.md) and `examples/`) |

Library primitives are **building blocks**. Prefer a named domain VO when the concept carries business language beyond “string” or “int”.
