<?php

declare(strict_types=1);

namespace Yadddl\ValueObject;

use Yadddl\ValueObject\Error\FieldError;
use Yadddl\ValueObject\Error\InvalidValueObject;
use Yadddl\ValueObject\Error\ValidationError;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;
use RuntimeException;

/**
 * @template T as object
 */
final class Factory
{
    /** @var class-string<T> $target */
    private string $target;

    private ReflectionClass $reflectionClass;
    private ?ReflectionMethod $constructor;

    /**
     * @param class-string<T> $target
     * @throws ReflectionException
     */
    public function __construct(string $target)
    {
        $this->target = $target;

        $this->reflectionClass = new ReflectionClass($this->target);
        $this->constructor = $this->reflectionClass->getConstructor();
    }

    /**
     * @param array $args
     *
     * @return T
     *
     * @throws ReflectionException
     */
    protected function newInstance(array $args): object
    {
        /** @var T */
        $instance = $this->reflectionClass->newInstanceWithoutConstructor();

        if ($this->constructor) {
            $this->constructor->setAccessible(true);
            $this->constructor->invokeArgs($instance, $args);
        }

        return $instance;
    }

    /**
     * @return string[]
     *
     * @psalm-return list<string>
     */
    private function getConstructorParameters(): array
    {
        $parameters = $this->constructor?->getParameters();

        return $parameters
            ? array_map(static fn (ReflectionParameter $parameter) => $parameter->getName(), $parameters)
            : [];
    }

    /**
     * @psalm-return T|ValidationError
     *
     * @throws ReflectionException
     */
    public function make(mixed ...$args): object
    {
        $errors = new ValidationError($this->target);

        $parameters = $this->getConstructorParameters();

        if (count($parameters) !== count($args)) {
            throw new RuntimeException('Wrong parameter count');
        }

        foreach ($parameters as $index => $parameter) {
            /** @var mixed */
            $argument = $args[$index];

            if (is_object($argument)) {
                match (get_class($argument)) {
                    InvalidValueObject::class => $errors->addError(new FieldError($parameter, $argument)),
                    ValidationError::class => $errors->merge($parameter, $argument)
                };
            }
        }

        if ($errors->hasErrors()) {
            return $errors;
        }

        return $this->newInstance($args);
    }
}
