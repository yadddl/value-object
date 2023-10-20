<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Error;

use JetBrains\PhpStorm\Pure;

class ValidationError extends \ValueError
{
    use FailableTrait;

    /** @var list<FieldError> */
    private array $errors = [];

    #[Pure] public function __construct(
        public readonly string $className,
        public readonly string $shortName
    ) {
        parent::__construct("Invalid {$this->shortName}");
    }

    public static function fromReflectionClass(\ReflectionClass $class): ValidationError {
        return new ValidationError($class->getName(), $class->getShortName());
    }

    public function hasErrors(): bool
    {
        return count($this->errors) > 0;
    }

    public function merge(string $prefix, ValidationError $valueObjectError): void
    {
        $errors = $valueObjectError->getErrors();

        foreach ($errors as $error) {
            $this->addError($error->addPrefix($prefix));
        }
    }

    /**
     * @psalm-return list<FieldError>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function addError(FieldError $error): void
    {
        $this->errors[] = $error;
    }

    /**
     * @return array{class : string, fields : array<string, string>}
     */
    #[Pure] public function getInvalidFields(): array
    {
        $invalidFields = [];

        foreach ($this->errors as $error) {
            $key = $error->key;
            $invalidFields[$key] = $error->message;
        }

        return [
            'class' => $this->className,
            'shortName' => $this->shortName,
            'message' => $this->message,
            'fields' => $invalidFields
        ];
    }
}
