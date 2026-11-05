# Structural serializer

Namespace: `Yadddl\Serializer`

Converts object graphs ↔ arrays/scalars for persistence adapters. **It does not validate.** Domain `create()` methods do.

## Quick use

```php
use Yadddl\Serializer\StructuralConverter;

$converter = StructuralConverter::create();

$payload = $converter->toArray($object);
$result = $converter->fromArray($payload, Person::class);
// Person|ErrorsBag|MultipleFieldErrorsBag
```

Lower-level:

```php
$serializer = new StructuralSerializer();
$deserializer = new FactoryDeserializer();

$serializer->serialize($object);
$deserializer->deserialize($data, Type::class);
```

---

## Serialize conventions

| Input | Output |
|-------|--------|
| `null` / scalar | unchanged |
| `ValidatedValue` | bare `$value` (recursively serialized) |
| `BackedEnum` | backing value |
| `UnitEnum` | case name |
| object | public instance properties → assoc array (recursive) |
| `array` / `Traversable` | array, **keys preserved** |

Custom per-type serializers (win over defaults):

```php
new StructuralSerializer([
    Age::class => fn (Age $age) => ['years' => $age->value],
]);
```

`StructuralConverter::create()` registers Date/Time/DateTime/DateTimeWithTimeZone as **strings** so they round-trip through `create(string)`.

Without that, Date serializes to `['days'=>…,'months'=>…,'years'=>…]` and deserializes via `Date::createFrom(...)`.

---

## Deserialize conventions

1. Resolve `create` or another public static `create*` whose **parameter names** match payload keys
2. For class-typed parameters, recurse (`Address` inside `Customer`)
3. Call the factory — never `newInstanceWithoutConstructor`
4. If the factory returns `ErrorsBag` / `MultipleFieldErrorsBag`, **return it**
5. If a nested deserialize returns an error bag, **return it** (do not build a half object)
6. Missing factory / unknown type / bad enum → `DeserializationError` (infrastructure, not validation)

### Scalar VO

```php
$converter->fromArray(25, Age::class); // Age::create(25)
```

### Associative composite

```php
$converter->fromArray([
    'name' => 'Jane',
    'email' => 'jane@example.com',
    'age' => 28,
], Person::class);
```

### Nested graph

```php
$converter->fromArray([
    'name' => 'Ada',
    'email' => 'ada@example.com',
    'address' => [
        'city' => 'London',
        'street' => 'Baker St',
    ],
], Customer::class);
```

### Optional factory parameters

Payload may omit keys that have **default values** on `create(...)`.

---

## Round-trip examples

### Age

```php
$age = Age::create(40)->orFail();
$payload = $converter->toArray($age);           // 40
$again = $converter->fromArray($payload, Age::class); // Age
```

### Person

```php
$person = Person::create('Jane', 'jane@example.com', 28)->orFail();
$payload = $converter->toArray($person);
/*
[
  'name' => 'Jane',
  'email' => 'jane@example.com',
  'age' => 28,
]
*/
$again = $converter->fromArray($payload, Person::class);
```

### Invalid payload

```php
$result = $converter->fromArray([
    'name' => '',
    'email' => 'nope',
    'age' => -1,
], Person::class);

// MultipleFieldErrorsBag — use at the entry point to render 422
```

### Password note

`Password` hashes on `create`. Serializing yields the hash; feeding that hash back into `create` would hash again. Treat passwords as write-only for round-trips, or add a dedicated reconstitute factory if you must load hashes.

---

## What not to expect

- No Symfony PropertyAccess / private getter discovery in 1.0
- No annotation/attribute mapping DSL
- No polymorphic type maps (out of MVP)
- No validation rules inside the serializer

Design domain factories first; the serializer only needs predictable `create` shapes and public state.
