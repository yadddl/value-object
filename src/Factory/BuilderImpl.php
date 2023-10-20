<?php

namespace Yadddl\ValueObject\Factory;

use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;
use Yadddl\ValueObject\Attributes\Collection;
use Yadddl\ValueObject\Error\FieldError;
use Yadddl\ValueObject\Error\ValidationError;
use Yadddl\ValueObject\Error\ValueError;

class BuilderImpl implements Builder
{
    protected function buildIterableArguments(ReflectionParameter $parameter, array $data): array|ValidationError
    {
        $errors = new ValidationError($parameter->name, $parameter->name);

        $attributes = $parameter->getAttributes(Collection::class);

        assert(count($attributes) !== 0, 'Missing Collection Attribute for the parameter ' . $parameter->name);
        assert(count($attributes) === 1, 'Cannot be more than one Collection Attribute defined for the parameter ' . $parameter->name);

        /** @var Collection $attributeInstance */
        $attributeInstance = $attributes[0]->newInstance();
        $type = $attributeInstance->className;

        $arguments = [];

        foreach ($data as $index => $item) {
            $instance = $this->build($type, $item);

            if ($instance instanceof ValidationError) {
                $errors->merge("[{$index}]", $instance);
            } else {
                $arguments[] = $instance;
            }
        }

        return $errors->hasErrors() ? $errors : $arguments;
    }

    protected function newInstance(ReflectionClass $class, array $args, ValidationError $errors = null): ?object
    {
        try {
            $instance = $class->newInstanceWithoutConstructor();
            $constructor = $class->getConstructor();

            if ($constructor) {
                $constructor->setAccessible(true);
                $constructor->invokeArgs($instance, $args);
            }

            return $instance;
        } catch (ValueError $error) {
            $errors = $errors ?? ValidationError::fromReflectionClass($class);
            $errors->addError(new FieldError($error->field, $error->getMessage(), $error));

            return $errors;
        }
    }

    public function build(string $type, array|int|float|string|bool $data): mixed
    {
        $class = new ReflectionClass($type);

        return match (true) {
            is_scalar($data) => $this->buildFromScalar($class, $data),
            is_array($data)  => $this->buildFromArray($class, $data),
        };
    }

    protected function buildFromScalar(ReflectionClass $class, float|bool|int|string $data): mixed
    {
        return $this->newInstance($class, [$data]);
    }

    protected function buildFromArray(ReflectionClass $class, array $data): mixed
    {
        $errors = ValidationError::fromReflectionClass($class);
        $parameters = $this->getConstructorParameters($class);
        $arguments = [];

        foreach ($parameters as $parameter) {
            $field = $parameter->name;
            $type = $parameter->getType();


            // check if the field is missing or not
            if ($this->parameterIsMissing($parameter, $data, $errors)) continue;
            if ($this->parameterHasWrongType($parameter, $errors)) continue;

            $value = $data[$field];
            $builtIn = $type->isBuiltin();

            if ($value === null) {

                $arguments[$field] = null;
            }
            else if ($builtIn) {
                if ($type !== null && $type->getName() === 'array') {
                    $instance = $this->buildIterableArguments($parameter, $value);

                    if ($instance instanceof ValidationError) {
                        $errors->merge($field, $instance);
                        $arguments[$field] = [];
                    } else {
                        $arguments[$field] = $instance;
                    }
                } else {
                    $arguments[$field] = $value;
                }
            } else {
                $instance = $this->build($type->getName(), $value);

                if ($instance instanceof ValidationError) {
                    $errors->merge($field, $instance);
                    $arguments[$field] = null;
                } else {
                    $arguments[$field] = $instance;
                }
            }
        }

        return $errors->hasErrors() ? $errors : $this->newInstance($class, $arguments, $errors);
    }

    /**
     * @param ReflectionClass $class
     * @return array<ReflectionParameter>
     */
    private function getConstructorParameters(ReflectionClass $class): array
    {
        return $class->getConstructor()?->getParameters() ?? [];
    }

    /**
     * @param array $data
     * @param string $field
     * @param ValidationError $errors
     * @return array
     */
    public function parameterIsMissing(ReflectionParameter $parameter, array &$data, ValidationError $errors): bool
    {
        $field = $parameter->name;
        $isMissing = !isset($data[$field]);
        $isRequired = !$parameter->isOptional();

        if ($isMissing && $isRequired) {
            $errors->addError(new FieldError($field, "Field {$field} is missing"));
            return true;
        }

        return false;
    }

    public function parameterHasWrongType(ReflectionParameter $parameter, ValidationError $errors): bool
    {
        if (!$parameter->getType() instanceof ReflectionNamedType) {
            $errors->addError(new FieldError($parameter->name, "Only arguments with single type supported"));
            return true;
        }

        return false;
    }
}