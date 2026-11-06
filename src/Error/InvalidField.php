<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Error;

/**
 * Field / value-object validation failure.
 *
 * Extend with a project-specific type (e.g. InvalidEmail). For generic
 * library validators that only have a message, use {@see InvalidValue}.
 */
abstract class InvalidField extends DomainError
{
}
