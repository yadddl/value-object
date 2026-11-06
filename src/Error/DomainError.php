<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Error;

/**
 * Base type for all domain failures.
 *
 * Extend {@see InvalidField} or {@see InvalidEntity} in consuming projects.
 * Do not throw this class directly — it is abstract on purpose.
 */
abstract class DomainError extends \RuntimeException
{
}
