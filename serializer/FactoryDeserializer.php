<?php

declare(strict_types=1);

namespace Yadddl\Serializer;

use ArgumentCountError;
use BackedEnum;
use ReflectionClass;
use ReflectionEnum;
use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use TypeError;
use UnitEnum;
use ValueError;
use Yadddl\ValueObject\Error\ErrorsBag;
use Yadddl\ValueObject\Error\MultipleFieldErrorsBag;

final class FactoryDeserializer implements Deserializer
{
    #[\Override]
    public function deserialize(mixed $data, string $type): mixed
    {
        if (enum_exists($type)) {
            /** @var class-string $type */
            return $this->deserializeEnum($type, $data);
        }

        if (!class_exists($type)) {
            throw new DeserializationError(sprintf('Unknown type "%s"', $type));
        }

        /** @var class-string $type */
        if (!method_exists($type, 'create') && !$this->hasMatchingFactory($type, $data)) {
            throw new DeserializationError(sprintf(
                'Type "%s" has no static create() factory for deserialization',
                $type,
            ));
        }

        try {
            return $this->invokeBestFactory($type, $data);
        } catch (DeserializationError $exception) {
            throw $exception;
        } catch (ArgumentCountError|TypeError|ValueError|ReflectionException $exception) {
            throw new DeserializationError(
                sprintf('Cannot deserialize "%s": %s', $type, $exception->getMessage()),
                previous: $exception,
            );
        }
    }

    /**
     * @param class-string $type
     */
    private function invokeBestFactory(string $type, mixed $data): mixed
    {
        if (!is_array($data)) {
            // Guaranteed by the create()/factory guard in deserialize()
            return $type::create($data);
        }

        /** @var array<array-key, mixed> $data */
        $factories = $this->candidateFactories($type);
        foreach ($factories as $method) {
            if ($this->canBind($method, $data)) {
                return $this->invoke($type, $method, $data);
            }
        }

        if (method_exists($type, 'create') && count($data) === 1 && array_is_list($data)) {
            return $type::create($data[0]);
        }

        throw new DeserializationError(sprintf(
            'No factory on "%s" matches payload keys [%s]',
            $type,
            implode(', ', array_map(strval(...), array_keys($data))),
        ));
    }

    /**
     * @param class-string $type
     * @return list<ReflectionMethod>
     */
    private function candidateFactories(string $type): array
    {
        $class = new ReflectionClass($type);
        $methods = [];

        if ($class->hasMethod('create')) {
            $create = $class->getMethod('create');
            if ($create->isStatic() && $create->isPublic()) {
                $methods[] = $create;
            }
        }

        foreach ($class->getMethods(ReflectionMethod::IS_STATIC | ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->getName() === 'create') {
                continue;
            }
            if (!str_starts_with($method->getName(), 'create')) {
                continue;
            }
            $methods[] = $method;
        }

        return $methods;
    }

    /**
     * @param class-string $type
     */
    private function hasMatchingFactory(string $type, mixed $data): bool
    {
        if (!is_array($data)) {
            return false;
        }

        foreach ($this->candidateFactories($type) as $method) {
            if ($this->canBind($method, $data)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<array-key, mixed> $data
     */
    private function canBind(ReflectionMethod $method, array $data): bool
    {
        foreach ($method->getParameters() as $parameter) {
            $name = $parameter->getName();
            if (array_key_exists($name, $data)) {
                continue;
            }
            // mixed allows null, so only treat truly optional params (default value) as skippable
            if ($parameter->isDefaultValueAvailable()) {
                continue;
            }

            return false;
        }

        return $method->getNumberOfParameters() > 0 || $data === [];
    }

    /**
     * @param class-string $type
     * @param array<array-key, mixed> $data
     */
    private function invoke(string $type, ReflectionMethod $method, array $data): mixed
    {
        $arguments = [];

        foreach ($method->getParameters() as $parameter) {
            $name = $parameter->getName();

            if (!array_key_exists($name, $data)) {
                // canBind() only allows missing keys when a default exists
                $arguments[$name] = $parameter->getDefaultValue();
                continue;
            }

            $resolved = $this->resolveArgument($parameter, $data[$name]);
            if ($resolved instanceof ErrorsBag || $resolved instanceof MultipleFieldErrorsBag) {
                return $resolved;
            }
            $arguments[$name] = $resolved;
        }

        return $method->invokeArgs(null, $arguments);
    }

    private function resolveArgument(ReflectionParameter $parameter, mixed $value): mixed
    {
        $type = $parameter->getType();
        if (!$type instanceof ReflectionNamedType || $type->isBuiltin()) {
            return $value;
        }

        $className = $type->getName();
        if (!class_exists($className) && !enum_exists($className)) {
            return $value;
        }

        if (is_object($value) && $value instanceof $className) {
            return $value;
        }

        return $this->deserialize($value, $className);
    }

    /**
     * @param class-string $type
     */
    private function deserializeEnum(string $type, mixed $data): UnitEnum
    {
        $reflection = new ReflectionEnum($type);

        if ($reflection->isBacked()) {
            try {
                /** @var class-string<BackedEnum> $type */
                return $type::from($data);
            } catch (ValueError $exception) {
                throw new DeserializationError(
                    sprintf('Invalid backed enum value for "%s"', $type),
                    previous: $exception,
                );
            }
        }

        if (!is_string($data)) {
            throw new DeserializationError(sprintf(
                'Unit enum "%s" expects a case name string',
                $type,
            ));
        }

        foreach ($reflection->getCases() as $case) {
            if ($case->getName() === $data) {
                return $case->getValue();
            }
        }

        throw new DeserializationError(sprintf('Unknown unit enum case "%s" for "%s"', $data, $type));
    }
}
