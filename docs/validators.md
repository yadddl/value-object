# Validator reference

Validators are **pipe steps**: static methods return `Closure(ValidationContext): ValidationContext`.

```php
$context = $raw
    |> IntegerValue::from(...)
    |> IntegerValue::min(0)
    |> IntegerValue::max(10);
```

`from()` starts a context (and usually applies a type check). Later steps accumulate errors without stopping the chain.

---

## `IntegerValue`

| Method | Role |
|--------|------|
| `from(mixed $value)` | Start pipeline; ensure integer |
| `isInteger(?string $message)` | Type check step |
| `min(int $min, ?string $message)` | `>= min` (skips if not int) |
| `max(int $max, ?string $message)` | `<= max` |
| `between(int $min, int $max, ?string $message)` | Inclusive range |

```php
$value
    |> IntegerValue::from(...)
    |> IntegerValue::between(1, 100, 'Pick 1–100');
```

---

## `StringValue`

| Method | Role |
|--------|------|
| `from(mixed $value)` | Start pipeline; ensure string |
| `isString(?string $message)` | Type check |
| `notEmpty(?string $message)` | Non-empty |
| `minLength` / `maxLength` | Length bounds |
| `email(?string $message)` | `FILTER_VALIDATE_EMAIL` |
| `url(?string $message)` | `FILTER_VALIDATE_URL` |
| `hasUppercase` / `hasLowercase` / `hasNumber` / `hasSpecialCharacter` | Character classes |
| `trim()` | Map: trim whitespace when still a string |
| `matches(string $pattern, ?string $message)` | Regex **without** changing the value |
| `regex(string $pattern, ?string $message)` | Regex and replace value with `preg_match` captures |

```php
// Keep the string
|> StringValue::matches('/^[A-Z]{3}$/', 'Use a 3-letter code')

// Parse captures (see examples/Money.php)
|> StringValue::regex('/^(\d+)([€$])$/u', 'Invalid money')
|> ArrayValue::mapKeys([1 => 'amount', 2 => 'currency'])
```

---

## `ArrayValue`

| Method | Role |
|--------|------|
| `from(mixed $value)` | Start pipeline; ensure array |
| `isArray(?string $message)` | Type check |
| `map(callable $mapper)` | Transform array when valid |
| `mapKeys(array $map)` | Rename keys (`old => new`) |
| `validateArray(callable $predicate, string $message)` | Predicate on the array |

Non-array contexts pass through map/validate steps unchanged (so earlier type errors remain the only failure).

---

## `BackedEnumValue`

Domain helper for **backed** enums (`string` or `int`). Returns `BackedEnum|ErrorsBag` — no exceptions on the validation path.

| Method | Role |
|--------|------|
| `create(class-string<BackedEnum>, mixed $value, ?string $invalidMessage)` | Resolve case from backing value, or pass through an existing case |

```php
use Yadddl\ValueObject\Validators\BackedEnumValue;

enum ListingStatus: string
{
    case Published = 'published';
    case Draft = 'draft';
}

$status = BackedEnumValue::create(ListingStatus::class, $raw);
// ListingStatus|ErrorsBag

// Already a case → returned as-is
BackedEnumValue::create(ListingStatus::class, ListingStatus::Draft);

// Custom message
BackedEnumValue::create(ListingStatus::class, $raw, 'Unknown listing status');
```

Works with `MultipleValidationContext` — on success the field value is the **enum instance**, not the backing scalar:

```php
$context = MultipleValidationContext::setup(
    status: BackedEnumValue::create(ListingStatus::class, $status),
    // …
);
// $context->getValues()['status'] instanceof ListingStatus
```

Domain methods (`isPublished()`, …) stay on your enum. Unit (non-backed) enums are not supported here — use the serializer’s throw-based path for those at infrastructure boundaries.

---

## `ValidationContext` utilities

Available on any context in the pipe:

| API | Role |
|-----|------|
| `isValid()` / `hasErrors()` | Status |
| `getValue()` | Current value (may have been mapped) |
| `getErrors(): ErrorsBag` | Accumulated messages |
| `then(callable)` | If valid, run callback with value; else return `ErrorsBag` |
| `ValidationContext::map(callable)` | Pipe step to map value when still valid |
| `ValidationContext::mapArray` / `validateArray` | Array-only helpers |
| `ValidationContext::mergeErrors(...$contexts)` | Merge several contexts into one bag |
| `ValidationContext::extractValues(array $fieldContexts)` | Field => value for valid contexts |

---

## `MultipleValidationContext`

See [composites.md](composites.md). Aggregates `ValidatedValue|BackedEnum|ErrorsBag` results by field name.
