# Changelog

All notable changes to `yadddl/value-object` are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0] — 2026-11-05

### Added

- `BackedEnumValue::create(class-string, mixed): BackedEnum|ErrorsBag` for domain validation of string/int-backed enums (no throw on invalid input)
- `MultipleValidationContext` accepts successful `BackedEnum` results and **keeps the enum instance** (does not unwrap to the backing scalar)
- Domain exception hierarchy: `DomainException` → `ValidationFailedException` / `DomainLogicException`
- Examples: `OrderStatus` / `Order` showing backed enums in `MultipleValidationContext`

### Changed

- `ErrorsBag::orFail()` / `MultipleFieldErrorsBag::orFail()` throw `ValidationFailedException` (bag via `getErrors()`) instead of `\InvalidArgumentException`
  — catch `DomainException` / `ValidationFailedException` at boundaries for 422 vs other statuses

## [1.0.0] — 2026-11-05

### Added

- PHP **8.5+** functional validation built on the pipe operator (`|>`)
- `ValidationContext` immutable functor with error accumulation
- Validator library: `IntegerValue`, `StringValue`, `ArrayValue`
- `MultipleValidationContext` for composite / entity field aggregation
- Error model: `Error`, `ErrorsBag`, `MultipleFieldErrorsBag`, `Failable`, `FailableSuccess`
- Contract `ValidatedValue` (`public $value`) for scalar-backed VOs
- Library primitives rewritten on pipes: `Text`, `NotEmptyText`, `Integer`, `PositiveInteger`, `StrictlyPositiveInteger`, `Email`, `Url`, `Uuid`
- `StringValue::url()` validator (`FILTER_VALIDATE_URL`)
- `Uuid` backed by `ramsey/uuid` (`generate()`, `fromRamsey()`, `toRamsey()`, lowercase storage)
- Composites: `Date`, `Time`, `DateTime`, `DateTimeWithTimeZone` returning `ErrorsBag` on failure
- `TimeZone` primitive (`utc()`, `fromDateTimeZone()`, `toDateTimeZone()`)

- In-package structural conversion under `Yadddl\Serializer`:
  - `StructuralSerializer` / `FactoryDeserializer` / `StructuralConverter`
  - `ValidatedValue` serializes to a **bare scalar**
  - Deserialize via `create` / matching `create*` factories
  - Domain `ErrorsBag` / `MultipleFieldErrorsBag` propagated to the caller
- Blog-aligned examples: `Age`, `Password`, `Person`, `Money`, …
- CI: Pest, Psalm, CS Fixer, **100%** unit coverage gate

### Changed

- Public VO API is `create(...): static|ErrorsBag` (or `MultipleFieldErrorsBag`) instead of exception-first constructors
- Equality helpers on new primitives/composites use `equals()`

### Removed

- PHP `< 8.5` support
- `cuyz/valinor` (declared but unused)
- PHPStan as a required tooling gate (Psalm only)
- Legacy `factory()` helper and reflection `Builder` / `BuilderImpl`
- Legacy throw-based `ValidationError`, `InvalidValueObject`, typed `ValueError` subclasses as the primary flow
- Attribute `#[Collection]` hydration path
- Standalone package role of `yadddl/serializer` (API absorbed here)

### Migration

See [docs/migration-from-0.8.md](docs/migration-from-0.8.md).

## [0.8.0] — 2023-10-20

Last release of the pre–PHP 8.5 hybrid API (constructor throws + reflection factory).
