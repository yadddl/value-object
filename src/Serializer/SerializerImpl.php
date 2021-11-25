<?php

declare(strict_types=1);

namespace Yadddl\DDD\Serializer;

use ReflectionClass;
use ReflectionProperty;
use Serializable;
use Stringable;
use Symfony\Component\PropertyAccess\PropertyAccess;

class SerializerImpl implements Serializer
{
    private SerializerConfig $config;

    public function __construct(SerializerConfig $config)
    {
        $this->config = $config;
    }

    /**
     * @psalm-return list<mixed>
     */
    private function serializeIterables(iterable $collection): array
    {
        $result = [];

        /** @var mixed */
        foreach ($collection as $item) {
            /** @var mixed */
            $result[] = $this->serialize($item);
        }

        return $result;
    }

    public function serialize(mixed $object): mixed
    {
        return match (true) {
            is_iterable($object) => $this->serializeIterables($object),
            is_object($object) => $this->serializeObjects($object),
            default => $object
        };
    }

    /**
     * @return ReflectionProperty[]
     */
    private function getProperties(object $object): array
    {
        $class = $object instanceof ReflectionClass
            ? $object
            : new ReflectionClass($object);

        $parentClass = $class->getParentClass();

        return $parentClass
            ? array_merge($this->getProperties($parentClass), $class->getProperties())
            : $class->getProperties();
    }

    private function serializeObjects(object $object): mixed
    {
        $serializer = $this->config->serializerFor($object);

        if ($serializer) {
            return $serializer->serialize($object);
        }

        if ($object instanceof Serializable) {
            return serialize($object);
        }

        if ($object instanceof Stringable) {
            return (string)$object;
        }

        $properties = $this->getProperties($object);

        $result = [];

        foreach ($properties as $property) {
            $name = $property->getName();

            $accessor = PropertyAccess::createPropertyAccessor();

            if ($accessor->isReadable($object, $name)) {
                /** @var mixed $value */
                $value = $accessor->getValue($object, $name);

                /** @var mixed */
                $result[$name] = $this->serialize($value);
            }
        }

        return $result;
    }
}
