<?php

declare(strict_types=1);

namespace Yadddl\DDD\Error;

use JetBrains\PhpStorm\Pure;

class ValidationError extends ValueError
{
    use FailableTrait;

    /** @var list<FieldError>  */
    private array $errors = [];

    #[Pure] public function __construct(private string $className)
    {
        parent::__construct("Invalid {$this->className}");
    }

    public function addError(FieldError $error): void
    {
        $this->errors[] = $error;
    }

    /**
     * @psalm-return list<FieldError>
     */
    public function getErrors(): array
    {
        return $this->errors;
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

    #[Pure] public function getInvalidFields(): array
    {
        $invalidFields = [];

        foreach ($this->errors as $error) {
            $key =  $error->getKey();
            $invalidFields[$key] = $error->getError()->getMessage();
        }

        return ['class'=> $this->className, 'fields' => $invalidFields];
    }
}
