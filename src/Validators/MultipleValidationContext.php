<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Validators;

use BackedEnum;
use Closure;
use InvalidArgumentException;
use ReflectionFunction;
use ReflectionNamedType;
use ReflectionUnionType;
use Yadddl\ValueObject\Contracts\ValidatedValue;
use Yadddl\ValueObject\Error\ErrorsBag;
use Yadddl\ValueObject\Error\MultipleFieldErrorsBag;

/**
 * Handles validation of multiple value objects for entity creation.
 * Collects all errors from multiple ValueObject|ErrorsBag|BackedEnum results.
 *
 * Prefer {@see map()} over {@see getValues()} when constructing aggregates:
 * the callback receives strongly typed success objects (VOs / enums), not an untyped array.
 */
readonly class MultipleValidationContext
{
    /**
     * @param array<string, mixed> $values Unwrapped scalars / enums for getValues() BC
     * @param array<string, ValidatedValue|BackedEnum> $objects Typed success objects for map()
     * @param MultipleFieldErrorsBag $errorsBag Errors organized by field name
     */
    private function __construct(
        private array $values,
        private array $objects,
        private MultipleFieldErrorsBag $errorsBag,
    ) {
    }

    /**
     * Setup validation context from multiple ValueObject|ErrorsBag|BackedEnum results.
     * Each named argument should be the result of a ValueObject::create() or {@see BackedEnumValue::create()} call.
     *
     * Successful {@see ValidatedValue} results unwrap to `$result->value` in {@see getValues()}.
     * Successful {@see BackedEnum} results are kept as the enum instance.
     * {@see map()} always receives the original success object (VO or enum).
     *
     * @param mixed ...$results Named arguments: fieldName => (ValidatedValue|BackedEnum|ErrorsBag)
     */
    public static function setup(...$results): static
    {
        $values = [];
        $objects = [];
        $errorsByField = [];

        foreach ($results as $fieldName => $result) {
            if ($result instanceof ErrorsBag) {
                $errorsByField[$fieldName] = $result->getErrors();
            } elseif ($result instanceof ValidatedValue) {
                $objects[$fieldName] = $result;
                $values[$fieldName] = $result->value;
            } elseif ($result instanceof BackedEnum) {
                $objects[$fieldName] = $result;
                $values[$fieldName] = $result;
            } else {
                throw new InvalidArgumentException(sprintf(
                    'Expected ErrorsBag, ValidatedValue, or BackedEnum for field "%s", got %s',
                    (string) $fieldName,
                    get_debug_type($result),
                ));
            }
        }

        return new static($values, $objects, MultipleFieldErrorsBag::fromArray($errorsByField));
    }

    public function isValid(): bool
    {
        return $this->errorsBag->isEmpty();
    }

    /**
     * Unwrapped values keyed by field name (BC / spreading into primitive constructors).
     *
     * @return array<string, mixed>
     */
    public function getValues(): array
    {
        return $this->values;
    }

    public function getErrors(): MultipleFieldErrorsBag
    {
        return $this->errorsBag;
    }

    /**
     * On invalid context, returns the error bag without invoking the callback.
     * On success, invokes $callback with named arguments bound to validated field objects
     * (ValidatedValue instances or BackedEnum cases — not unwrapped scalars).
     *
     * Callback parameter names must match setup field names. Parameter order may differ.
     * Optional callback parameters may omit a field.
     *
     * @template T
     *
     * @param callable(): T $callback
     *
     * @return T|MultipleFieldErrorsBag
     */
    public function map(callable $callback): mixed
    {
        if (! $this->isValid()) {
            return $this->errorsBag;
        }

        $closure = $callback instanceof Closure ? $callback : Closure::fromCallable($callback);
        $reflection = new ReflectionFunction($closure);
        $namedArgs = [];

        foreach ($reflection->getParameters() as $parameter) {
            $name = $parameter->getName();

            if (! array_key_exists($name, $this->objects)) {
                if ($parameter->isOptional()) {
                    continue;
                }

                throw new InvalidArgumentException(sprintf(
                    'map() parameter "$%s" does not match a validated field; known fields: %s',
                    $name,
                    $this->objects === [] ? '(none)' : implode(', ', array_keys($this->objects)),
                ));
            }

            $value = $this->objects[$name];
            $this->assertCompatibleType($name, $value, $parameter->getType());
            $namedArgs[$name] = $value;
        }

        return $closure(...$namedArgs);
    }

    private function assertCompatibleType(string $field, mixed $value, mixed $type): void
    {
        if ($type === null) {
            return;
        }

        if ($type instanceof ReflectionUnionType) {
            foreach ($type->getTypes() as $inner) {
                if ($inner instanceof ReflectionNamedType && $this->matchesNamedType($value, $inner)) {
                    return;
                }
            }

            throw new InvalidArgumentException(sprintf(
                'map() parameter "$%s" type mismatch: got %s',
                $field,
                get_debug_type($value),
            ));
        }

        if ($type instanceof ReflectionNamedType && ! $this->matchesNamedType($value, $type)) {
            throw new InvalidArgumentException(sprintf(
                'map() parameter "$%s" expects %s, got %s',
                $field,
                $type->getName(),
                get_debug_type($value),
            ));
        }
    }

    private function matchesNamedType(mixed $value, ReflectionNamedType $type): bool
    {
        $name = $type->getName();

        if ($type->isBuiltin()) {
            return match ($name) {
                'mixed' => true,
                'object' => is_object($value),
                default => get_debug_type($value) === $name,
            };
        }

        return $value instanceof $name;
    }
}
