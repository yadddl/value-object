<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Primitive;

use Yadddl\ValueObject\Error\DomainError;
use Yadddl\ValueObject\Error\ErrorsBag;
use Yadddl\ValueObject\Error\InvalidValue;
use Yadddl\ValueObject\Validators\ValidationContext;

/**
 * Shared labeling + typed failure mapping for primitive VOs.
 *
 * - {@see LABEL}: optional human name; empty → short class name
 * - {@see domainError()}: override to map failure codes to domain errors
 */
trait LabeledPrimitive
{
    /**
     * Optional display label for validation messages.
     * Empty string means "use the short class name".
     */
    protected const LABEL = '';

    protected static function label(): string
    {
        if (static::LABEL !== '') {
            return static::LABEL;
        }

        $class = static::class;
        $pos = strrpos($class, '\\');

        return $pos === false ? $class : substr($class, $pos + 1);
    }

    /**
     * Map a structured validation failure to a domain error.
     * Default: generic {@see InvalidValue} with the personalized message.
     */
    protected static function domainError(string $code, string $message): DomainError
    {
        return new InvalidValue($message);
    }

    protected static function failuresFrom(ValidationContext $context): ErrorsBag
    {
        return new ErrorsBag(array_map(
            static fn (array $failure) => static::domainError($failure['code'], $failure['message']),
            $context->getFailures(),
        ));
    }
}
