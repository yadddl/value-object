<?php

declare(strict_types=1);

namespace Yadddl\Serializer;

use BackedEnum;
use ReflectionClass;
use ReflectionProperty;
use Traversable;
use UnitEnum;
use Yadddl\ValueObject\Contracts\ValidatedValue;

final class StructuralSerializer implements Serializer
{
    /**
     * @param array<class-string, callable(object): mixed> $typeSerializers
     */
    public function __construct(
        private array $typeSerializers = [],
    ) {
    }

    #[\Override]
    public function serialize(mixed $value): mixed
    {
        if ($value === null || is_scalar($value)) {
            return $value;
        }

        if (is_array($value)) {
            return $this->serializeIterable($value);
        }

        if ($value instanceof Traversable) {
            return $this->serializeIterable(iterator_to_array($value));
        }

        if (!is_object($value)) {
            return $value;
        }

        foreach ($this->typeSerializers as $className => $serializer) {
            if ($value instanceof $className) {
                return $serializer($value);
            }
        }

        if ($value instanceof BackedEnum) {
            return $value->value;
        }

        if ($value instanceof UnitEnum) {
            return $value->name;
        }

        if ($value instanceof ValidatedValue) {
            return $this->serialize($value->value);
        }

        return $this->serializeObjectProperties($value);
    }

    /**
     * @param array<array-key, mixed> $values
     * @return array<array-key, mixed>
     */
    private function serializeIterable(array $values): array
    {
        $result = [];
        foreach ($values as $key => $item) {
            $result[$key] = $this->serialize($item);
        }

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeObjectProperties(object $object): array
    {
        $result = [];

        foreach ($this->publicProperties($object) as $property) {
            $result[$property->getName()] = $this->serialize($property->getValue($object));
        }

        return $result;
    }

    /**
     * @return list<ReflectionProperty>
     */
    private function publicProperties(object $object): array
    {
        $class = new ReflectionClass($object);
        $properties = [];

        do {
            foreach ($class->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
                if ($property->isStatic()) {
                    continue;
                }
                $properties[] = $property;
            }
            $class = $class->getParentClass() ?: null;
        } while ($class !== null);

        return $properties;
    }
}
